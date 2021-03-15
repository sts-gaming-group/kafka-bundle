<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\Client\Consumer\Message;
use Sts\KafkaBundle\Exception\KafkaException;
use Sts\KafkaBundle\RdKafka\Context;

interface ConsumerInterface extends ClientInterface
{
    public function consume(Message $message, Context $context): bool;
    public function handleException(KafkaException $kafkaException, RdKafkaMessage $message, Context $context): bool;
    public function getName(): string;
}
