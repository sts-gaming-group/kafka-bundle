<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\ConfigurationContainer;
use Sts\KafkaBundle\Configuration\Contract\ValidatedConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class OffsetStoreMethod implements ValidatedConfigurationInterface
{
    public const BROKER = 'broker';
    public const FILE = 'file';

    public const NAME = 'offset_store_method';

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
            'Offset commit store method: 
            - %1$s - local file store (offset.store.path, et.al), 
            - %2$s - broker commit store (requires Apache Kafka 0.8.2 or later on the broker). Defaults to $2%s',
            self::FILE,
            self::BROKER
        );
    }

    public function validate(ConfigurationContainer $configuration): bool
    {
        return in_array($configuration->getConfiguration(self::NAME), [self::BROKER, self::FILE], true);
    }

    public function validationError(ConfigurationContainer $configuration): string
    {
        return sprintf(
            'Store method must be either %s or %s. %s given.',
            self:: BROKER,
            self::FILE,
            $configuration->getConfiguration(self::NAME)
        );
    }

    public function getDefaultValue(): string
    {
        return self::BROKER;
    }
}
