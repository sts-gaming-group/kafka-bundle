<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use Sts\KafkaBundle\Configuration\Traits\ObjectConfigurationTrait;
use Sts\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;
use Sts\KafkaBundle\Denormalizer\PlainDenormalizer;
use Symfony\Component\Console\Input\InputOption;

class Denormalizer implements ConsumerConfigurationInterface
{
    use ObjectConfigurationTrait;

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

    public static function getDefaultValue(): string
    {
        return PlainDenormalizer::class;
    }

    protected function getInterface(): string
    {
        return DenormalizerInterface::class;
    }
}
