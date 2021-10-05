<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Type\Validators;
use Sts\KafkaBundle\Tests\Dummy\Validator\DummyValidatorOne;

class ValidatorsTest extends AbstractObjectConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new Validators();
    }

    protected function getObject(): object
    {
        return new DummyValidatorOne();
    }
}
