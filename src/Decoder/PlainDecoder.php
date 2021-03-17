<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Decoder;

use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;

class PlainDecoder implements DecoderInterface
{
    public function decode(ResolvedConfiguration $configuration, string $message): string
    {
        return $message;
    }
}
