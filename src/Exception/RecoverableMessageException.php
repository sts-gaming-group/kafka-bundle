<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Exception;

use Throwable;

class RecoverableMessageException extends \RuntimeException
{
    public function __construct(Throwable $throwable)
    {
        parent::__construct($throwable->getMessage(), $throwable->getCode(), $throwable->getPrevious());
    }
}
