<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use Sts\KafkaBundle\Validator\Contract\ValidatorInterface;
use Sts\KafkaBundle\Validator\Type\PlainValidator;
use Symfony\Component\Console\Input\InputOption;

class Validators implements ConsumerConfigurationInterface
{
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

    public function isValueValid($value): bool
    {
        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            $classImplements = class_implements($item);
            if (!$classImplements) {
                return false;
            }
            if (!class_exists($item) || !in_array(ValidatorInterface::class, $classImplements, true)) {
                return false;
            }
        }

        return true;
    }

    public static function getDefaultValue(): array
    {
        return [PlainValidator::class];
    }
}
