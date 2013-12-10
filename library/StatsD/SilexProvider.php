<?php
namespace StatsD;

use Silex\ServiceProviderInterface;
use Silex\Application;

class SilexProvider implements ServiceProviderInterface
{
    public function boot(Application $app)
    {
    }

    public function register(Application $app)
    {
        $app['statsd'] = $app->share(function() use ($app) {
            \StatsD::init(array(
                'enabled' => $app['statsd.enabled'],
                'host'    => $app['statsd.host']
            ));

            // StatsD only has static methods. We abuse the fact
            // that PHP let's us call them on instances, too.
            return new \StatsD;
        });
    }
}
