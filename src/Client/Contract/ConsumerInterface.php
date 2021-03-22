<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Contract;

use Sts\KafkaBundle\Client\Consumer\Message;
use Sts\KafkaBundle\Exception\KafkaException;
use Sts\KafkaBundle\RdKafka\Context;

interface ConsumerInterface extends ClientInterface
{
    /**
     * @param Message $message
     * @param Context $context
     * @return mixed
     */
    public function consume(Message $message, Context $context);

    /**
     * @param KafkaException $exception
     * @param Context $context
     * @return mixed
     */
    public function handleException(KafkaException $exception, Context $context);
    public function getName(): string;
}
