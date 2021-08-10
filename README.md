# Laravel Mail Failover

A service provider for Laravel which registers Swift_FailoverTransport. Swift_FailoverTransport accepts a list of transports and provides graceful fallback if one transport fails.

[This feature was already requested in 2014](https://github.com/laravel/framework/issues/3374) but it was declined.
