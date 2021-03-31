<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Exception;

use Throwable;

class RecoverableMessageException extends \RuntimeException
{
    private array $data;

    public function __construct(Throwable $throwable, array $data = [])
    {
        $this->data = $data;
        
        parent::__construct($throwable->getMessage(), $throwable->getCode(), $throwable->getPrevious());
    }

    public function getData(): array
    {
        return $this->data;
    }
}
