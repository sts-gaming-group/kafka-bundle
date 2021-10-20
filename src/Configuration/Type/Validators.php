<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Traits\ObjectConfigurationTrait;
use StsGamingGroup\KafkaBundle\Validator\Contract\ValidatorInterface;
use StsGamingGroup\KafkaBundle\Validator\Type\PlainValidator;
use Symfony\Component\Console\Input\InputOption;

class Validators implements ConsumerConfigurationInterface
{
    use ObjectConfigurationTrait;

    public const NAME = 'validators';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY;
    }

    public function getDescription(): string
    {
        return sprintf(
            <<<EOT
            'Which validators to use after/before payload has been denormalized. Must implement %s.
            Defaults to %s which returns true by default.',
            EOT,
            ValidatorInterface::class,
            PlainValidator::class
        );
    }

    public function getDefaultValue(): array
    {
        return [PlainValidator::class];
    }

    protected function getInterface(): string
    {
        return ValidatorInterface::class;
    }
}
