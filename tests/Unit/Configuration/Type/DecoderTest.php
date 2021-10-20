<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Type\Decoder;
use StsGamingGroup\KafkaBundle\Tests\Dummy\Decoder\DummyDecoderOne;

class DecoderTest extends AbstractObjectConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new Decoder();
    }

    protected function getObject(): object
    {
        return new DummyDecoderOne();
    }
}
