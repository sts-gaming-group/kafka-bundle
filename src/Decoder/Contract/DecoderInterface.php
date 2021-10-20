<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Decoder\Contract;

use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;

interface DecoderInterface
{
    /**
     * @param ResolvedConfiguration $configuration
     * @param string $message
     * @return mixed
     */
    public function decode(ResolvedConfiguration $configuration, string $message);
}
