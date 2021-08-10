<?php

namespace CodingSocks\MailFailover\Tests;

use CodingSocks\MailFailover\MailFailoverServiceProvider;
use Illuminate\Mail\Transport\ArrayTransport;
use Orchestra\Testbench\TestCase;

class MailFailoverTransportTest extends TestCase
{
    /**
     * {@inheritDoc}
     */
    protected function getPackageProviders($app)
    {
        return [
            MailFailoverServiceProvider::class,
        ];
    }

    public function testGetFailoverTransportWithConfiguredTransports()
    {
        $this->app['config']->set('mail.default', 'failover');

        $this->app['config']->set('mail.mailers', [
            'failover' => [
                'transport' => 'failover',
                'mailers' => [
                    'sendmail',
                    'array',
                ],
            ],

            'sendmail' => [
                'transport' => 'sendmail',
                'path' => '/usr/sbin/sendmail -bs',
            ],

            'array' => [
                'transport' => 'array',
            ],
        ]);

        $transport = app('mailer')->getSwiftMailer()->getTransport();
        $this->assertInstanceOf(\Swift_FailoverTransport::class, $transport);

        $transports = $transport->getTransports();
        $this->assertCount(2, $transports);
        $this->assertInstanceOf(\Swift_SendmailTransport::class, $transports[0]);
        $this->assertInstanceOf(ArrayTransport::class, $transports[1]);
    }
}
