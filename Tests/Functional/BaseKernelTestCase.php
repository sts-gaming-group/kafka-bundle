<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Functional;

use Sts\KafkaBundle\Tests\AppKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class BaseKernelTestCase extends KernelTestCase
{
    protected function tearDown(): void
    {
        $dir = __DIR__ . '/../cache';

        $fs = new Filesystem();
        $fs->remove($dir);
    }

    protected static function getKernelClass(): string
    {
        require_once __DIR__.'/../AppKernel.php';

        return AppKernel::class;
    }

    protected static function createKernel(array $options = [])
    {
        $class = self::getKernelClass();

        return new $class(
            $options['test_config'] ?? null,
            $options['environment'] ?? 'test',
            $options['debug'] ?? true
        );
    }
}
