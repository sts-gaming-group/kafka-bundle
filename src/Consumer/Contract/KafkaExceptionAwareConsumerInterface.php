<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Consumer\Contract;

use Sts\KafkaBundle\Exception\KafkaException;

interface KafkaExceptionAwareConsumerInterface extends ConsumerInterface
{
    public function handleException(KafkaException $kafkaException): bool;
}
