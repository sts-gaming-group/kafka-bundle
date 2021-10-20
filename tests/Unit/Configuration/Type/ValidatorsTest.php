<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Unit\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Type\Validators;
use StsGamingGroup\KafkaBundle\Tests\Dummy\Validator\DummyValidatorOne;

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
