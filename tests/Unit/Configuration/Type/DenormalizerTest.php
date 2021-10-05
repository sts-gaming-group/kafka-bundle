<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Type\Denormalizer;
use Sts\KafkaBundle\Tests\Dummy\Denormalizer\DummyDenormalizerOne;

class DenormalizerTest extends AbstractObjectConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new Denormalizer();
    }

    protected function getObject(): object
    {
        return new DummyDenormalizerOne();
    }
}
