<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Decoder;

use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;
use StsGamingGroup\KafkaBundle\Decoder\Contract\DecoderInterface;

class JsonDecoder implements DecoderInterface
{
    public function decode(ResolvedConfiguration $configuration, string $message): array
    {
        return json_decode($message, true, 512, JSON_THROW_ON_ERROR);
    }
}
