<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;
use Sts\KafkaBundle\Denormalizer\PlainDenormalizer;
use Symfony\Component\Console\Input\InputOption;

class Denormalizer implements ConfigurationInterface
{
    public const NAME = 'denormalizer';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getDescription(): string
    {
        return sprintf(
            <<<EOT
            'Which denormalizer to use. Denormalizer is called after payload has been decoded. 
            Denormalizers must implement %s. Defaults to %s which just returns decoded payload.',
            EOT,
            DenormalizerInterface::class,
            PlainDenormalizer::class
        );
    }

    public function isValueValid($value): bool
    {
        $classImplements = class_implements($value);
        if (!$classImplements) {
            return false;
        }

        return class_exists($value) && in_array(DenormalizerInterface::class, $classImplements, true);
    }

    public static function getDefaultValue(): string
    {
        return PlainDenormalizer::class;
    }
}
