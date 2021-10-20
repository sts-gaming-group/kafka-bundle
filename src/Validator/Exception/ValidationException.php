<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Validator\Exception;

use StsGamingGroup\KafkaBundle\Validator\Contract\ValidatorInterface;

class ValidationException extends \RuntimeException
{
    private ValidatorInterface $validator;
    private string $failedReason;
    /**
     * @var mixed $data
     */
    private $data;

    /**
     * ValidationException constructor.
     * @param ValidatorInterface $validator
     * @param string $failedReason
     * @param mixed $data
     * @param string $message
     */
    public function __construct(ValidatorInterface $validator, string $failedReason, $data, string $message)
    {
        $this->validator = $validator;
        $this->failedReason = $failedReason;
        $this->data = $data;

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

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
