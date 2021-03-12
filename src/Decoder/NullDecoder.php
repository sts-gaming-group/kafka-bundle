<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Decoder;

use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;

class NullDecoder implements DecoderInterface
{
    public function decode(ResolvedConfiguration $resolvedConfiguration, string $message): string
    {
        return $message;
    }
}
