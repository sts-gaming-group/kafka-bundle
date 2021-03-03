<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Exception;

use RdKafka\Message as RdKafkaMessage;
use Sts\KafkaBundle\RdKafka\Context;

class KafkaException extends \RuntimeException
{
    private RdKafkaMessage $rdKafkaMessage;
    private Context $context;

    public function __construct(\Exception $exception, RdKafkaMessage $rdKafkaMessage, Context $context)
    {
        $this->rdKafkaMessage = $rdKafkaMessage;
        $this->context = $context;

        parent::__construct($exception->getMessage(), $exception->getCode(), $exception->getPrevious());
    }

    public function getRdKafkaMessage(): RdKafkaMessage
    {
        return $this->rdKafkaMessage;
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
