<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Decoder\Contract;

use Sts\KafkaBundle\Configuration\ResolvedConfiguration;

interface DecoderInterface
{
    /**
     * @param ResolvedConfiguration $configuration
     * @param string $message
     * @return mixed
     */
    public function decode(ResolvedConfiguration $configuration, string $message);
}
