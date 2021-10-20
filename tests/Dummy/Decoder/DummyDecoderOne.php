<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Dummy\Decoder;

use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;
use StsGamingGroup\KafkaBundle\Decoder\Contract\DecoderInterface;

class DummyDecoderOne implements DecoderInterface
{
    public function decode(ResolvedConfiguration $configuration, string $message): string
    {
        return $message;
    }
}
