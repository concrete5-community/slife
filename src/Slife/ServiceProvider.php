<?php

namespace Slife;

use Concrete\Core\Foundation\Service\Provider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function register()
    {
        /*
         * Example usage:
         * $app->make('slife', ['handle-of-the-webhook']);
         */
        $this->app->singleton('slife', function ($app, $params = []) {
            $client = $app->make('http/client/curl');
            $em = $app->make('Doctrine\ORM\EntityManager');

            /** @var \Slife\Manager $manager */
            $manager = $app->make('\Slife\Manager', [$app, $em, $client]);

            if (count($params)) {
                $manager->setWebhook($params[0]);
            }

            return $manager;
        });
    }
}
