<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Validator\Contract;

interface ValidatorInterface
{
    /**
     * @param mixed $data
     * @return bool
     */
    public function validate($data): bool;

    /**
     * @param mixed $data
     * @return string
     */
    public function failureReason($data): string;

    public function type(): string;
}
