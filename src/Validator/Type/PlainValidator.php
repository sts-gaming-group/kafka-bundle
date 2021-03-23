<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Validator\Type;

use Sts\KafkaBundle\Validator\Contract\ValidatorInterface;
use Sts\KafkaBundle\Validator\Validator;

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
