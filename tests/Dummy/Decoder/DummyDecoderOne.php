<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Dummy\Decoder;

use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;

class DummyDecoderOne implements DecoderInterface
{
    public function decode(ResolvedConfiguration $configuration, string $message): string
    {
        return $message;
    }
}
