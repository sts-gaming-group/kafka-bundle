<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Validator\Type;

use StsGamingGroup\KafkaBundle\Validator\Contract\ValidatorInterface;
use StsGamingGroup\KafkaBundle\Validator\Validator;

class PlainValidator implements ValidatorInterface
{
    public function validate($data): bool
    {
        return true;
    }

    public function failureReason($data): string
    {
        return '';
    }

    public function type(): string
    {
        return Validator::PRE_DENORMALIZE_TYPE;
    }
}
