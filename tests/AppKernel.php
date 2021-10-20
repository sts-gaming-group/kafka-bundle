<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests;

use StsGamingGroup\KafkaBundle\StsGamingGroupKafkaBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            new StsGamingGroupKafkaBundle(),
            new FrameworkBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/base.xml');
    }
}
