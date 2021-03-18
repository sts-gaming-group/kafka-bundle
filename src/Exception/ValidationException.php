<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Exception;

use Sts\KafkaBundle\Validator\Contract\ValidatorInterface;

class ValidationException extends \RuntimeException
{
    private ValidatorInterface $validator;
    private string $failedReason;

    public function __construct(ValidatorInterface $validator, string $failedReason, string $message)
    {
        $this->validator = $validator;
        $this->failedReason = $failedReason;

        parent::__construct($message);
    }

    public function getFailedValidator(): ValidatorInterface
    {
        return $this->validator;
    }

    public function getFailedReason(): string
    {
        return $this->failedReason;
    }
}
