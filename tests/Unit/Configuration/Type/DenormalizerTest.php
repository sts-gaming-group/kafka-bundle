<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Type\Denormalizer;
use StsGamingGroup\KafkaBundle\Tests\Dummy\Denormalizer\DummyDenormalizerOne;

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
