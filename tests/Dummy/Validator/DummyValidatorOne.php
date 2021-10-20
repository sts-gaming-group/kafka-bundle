<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Dummy\Validator;

use StsGamingGroup\KafkaBundle\Validator\Contract\ValidatorInterface;
use StsGamingGroup\KafkaBundle\Validator\Validator;

class DummyValidatorOne implements ValidatorInterface
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
