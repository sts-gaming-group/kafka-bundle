<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Exception;

use Throwable;

class KafkaException extends \RuntimeException
{
    private Throwable $throwable;

    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;

        parent::__construct($throwable->getMessage(), $throwable->getCode(), $throwable->getPrevious());
    }

    public function getThrowable(): Throwable
    {
        return $this->throwable;
    }
}
