<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Consumer\Contract;

use Sts\KafkaBundle\Configuration\ConfigurationContainer;
use Sts\KafkaBundle\Consumer\Message;

interface ConsumerInterface
{
    public function consume(ConfigurationContainer $configuration, Message $message): bool;
    public function getName(): string;
}
