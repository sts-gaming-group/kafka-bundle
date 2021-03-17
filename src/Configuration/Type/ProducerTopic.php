<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Client\Traits\CheckProducerTopic;
use Sts\KafkaBundle\Configuration\Contract\ProducerConfigurationInterface;
use Sts\KafkaBundle\Exception\BlacklistTopicException;
use Symfony\Component\Console\Input\InputOption;

class ProducerTopic implements ProducerConfigurationInterface
{
    use CheckProducerTopic;

    public const NAME = 'producer_topic';

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
        return 'Producer topic name.';
    }

    public function isValueValid($value): bool
    {
        if (!is_string($value) && '' === $value) {
            return false;
        }

        try {
            $this->isTopicBlacklisted($value);
        } catch (BlacklistTopicException $exception) {
            return false;
        }

        return true;
    }

    public static function getDefaultValue(): string
    {
        return 'sts_kafka_producer_topic';
    }
}
