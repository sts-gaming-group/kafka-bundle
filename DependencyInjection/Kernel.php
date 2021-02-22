<?php

namespace Sts\KafkaBundle\DependencyInjection;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->import('../Resources/config/{packages}/*.xml');
        $container->import('../Resources/config/{packages}/'.$this->environment.'/*.xml');

        if (is_file(\dirname(__DIR__).'../Resources/config/services.xml')) {
            $container->import('../config/services.xml');
            $container->import('../config/{services}_'.$this->environment.'.xml');
        } elseif (is_file($path = \dirname(__DIR__).'../Resources/config/services.php')) {
            (require $path)($container->withPath($path), $this);
        }
    }

    protected function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import('../Resources/config/{routes}/'.$this->environment.'/*.xml');
        $routes->import('../Resources/config/{routes}/*.xml');

        if (is_file(\dirname(__DIR__).'../Resources/config/routes.xml')) {
            $routes->import('../config/routes.yaml');
        } elseif (is_file($path = \dirname(__DIR__).'../Resources/config/routes.php')) {
            (require $path)($routes->withPath($path), $this);
        }
    }
}
