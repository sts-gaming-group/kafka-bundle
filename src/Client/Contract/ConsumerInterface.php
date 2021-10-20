<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Client\Contract;

use StsGamingGroup\KafkaBundle\Client\Consumer\Message;
use StsGamingGroup\KafkaBundle\RdKafka\Context;

interface ConsumerInterface extends ClientInterface
{
    /**
     * @param Message $message
     * @param Context $context
     * @return mixed
     */
    public function consume(Message $message, Context $context);

    /**
     * @param \Exception $exception
     * @param Context $context
     * @return mixed
     */
    public function handleException(\Exception $exception, Context $context);
    public function getName(): string;
}
