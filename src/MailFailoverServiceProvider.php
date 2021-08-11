<?php

namespace CodingSocks\MailFailover;

use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Swift_FailoverTransport as FailoverTransport;

class MailFailoverServiceProvider extends ServiceProvider
{
    /**
     * Register the Swift Transport instance.
     *
     * @return void
     */
    public function register()
    {
        $this->app->afterResolving(MailManager::class, function (MailManager $manager) {
            $manager->extend('failover', function ($config) use ($manager) {
                $transports = [];
                foreach ($config['mailers'] as $name) {
                    $config = $this->getConfig($name);

                    if (is_null($config)) {
                        throw new InvalidArgumentException("Mailer [{$name}] is not defined.");
                    }

                    // Here we will check if the "driver" key exists and if it does we will set
                    // transport configuration parameter in order to  provide "BC" for any
                    // Laravel <= 6.x style mail configuration files.
                    $transports[] = $this->app['config']['mail.driver']
                        ? $manager->createTransport(array_merge($config, ['transport' => $name]))
                        : $manager->createTransport($config);
                }
                return new FailoverTransport($transports);
            });
        });
    }

    /**
     * Get the mail connection configuration.
     *
     * @param string $name
     *
     * @return array
     */
    protected function getConfig(string $name)
    {
        // Here we will check if the "driver" key exists and if it does we will use
        // the entire mail configuration file as the "driver" config in order to
        // provide "BC" for any Laravel <= 6.x style mail configuration files.
        return $this->app['config']['mail.driver']
            ? $this->app['config']['mail']
            : $this->app['config']["mail.mailers.{$name}"];
    }
}
