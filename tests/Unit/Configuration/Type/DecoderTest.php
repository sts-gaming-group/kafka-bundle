<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Type\Decoder;
use Sts\KafkaBundle\Tests\Dummy\Decoder\DummyDecoderOne;

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
