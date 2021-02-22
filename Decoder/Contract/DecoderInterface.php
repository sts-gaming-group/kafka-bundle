<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Decoder\Contract;

use Sts\KafkaBundle\Configuration\ConfigurationContainer;

interface DecoderInterface
{
    public function decode(ConfigurationContainer $configuration, string $message);
}
