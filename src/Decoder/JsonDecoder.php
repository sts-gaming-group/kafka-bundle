<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Decoder;

use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;

class JsonDecoder implements DecoderInterface
{
    public function decode(ResolvedConfiguration $configuration, string $message): array
    {
        return json_decode($message, true, 512, JSON_THROW_ON_ERROR);
    }
}
