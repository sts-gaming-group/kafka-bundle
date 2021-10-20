<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\KafkaConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ProducerConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Brokers implements KafkaConfigurationInterface, ConsumerConfigurationInterface, ProducerConfigurationInterface
{
    public const NAME = 'brokers';

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
        return 'Comma-separated list of brokers in the format: broker1,broker2 i.e. 172.0.0.1:9092,127.0.0.2:9092';
    }

    public function isValueValid($value): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }

        foreach ($value as $broker) {
            if (!is_string($broker) || '' === $broker) {
                return false;
            }
        }

        return true;
    }

    public function getKafkaProperty(): string
    {
        return 'metadata.broker.list';
    }

    public function getDefaultValue(): array
    {
        return ['127.0.0.1', '127.0.0.2'];
    }
}
