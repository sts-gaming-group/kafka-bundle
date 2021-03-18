<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Validator\Contract;

interface ValidatorInterface
{
    /**
     * @param mixed $denormalized
     * @return bool
     */
    public function validate($denormalized): bool;

    /**
     * @param mixed $denormalized
     * @return string
     */
    public function failureReason($denormalized): string;
}
