<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Validator;

use Sts\KafkaBundle\Validator\Contract\ValidatorInterface;

class PlainValidator implements ValidatorInterface
{
    public function validate($denormalized): bool
    {
        return true;
    }
}
