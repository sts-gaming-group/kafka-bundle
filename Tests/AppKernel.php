<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests;

use Sts\KafkaBundle\StsKafkaBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    private ?string $testConfig;

    public function __construct(?string $testConfig, string $env, bool $debug)
    {
        $this->testConfig = $testConfig;

        parent::__construct($env, $debug);
    }

    public function registerBundles(): array
    {
        return [
            new StsKafkaBundle(),
            new FrameworkBundle()
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        if ($this->testConfig) {
            $loader->load($this->testConfig);
        }

        $loader->load(__DIR__ . '/Functional/base.xml');
    }

    public function getCacheDir(): string
    {
        return __DIR__ . '/cache/' . spl_object_hash($this);
    }
}
