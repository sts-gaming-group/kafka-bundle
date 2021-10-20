<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Decoder;

use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;
use StsGamingGroup\KafkaBundle\Decoder\Contract\DecoderInterface;

class PlainDecoder implements DecoderInterface
{
    public function decode(ResolvedConfiguration $configuration, string $message): string
    {
        return $message;
    }
}
