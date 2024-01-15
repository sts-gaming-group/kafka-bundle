<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\CastValueInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Contract\KafkaConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Traits\SupportsConsumerTrait;
use Symfony\Component\Console\Input\InputOption;

class MaxPollIntervalMs implements ConsumerConfigurationInterface, KafkaConfigurationInterface, CastValueInterface
{
    use SupportsConsumerTrait;

    public const NAME = 'max_poll_interval_ms';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getKafkaProperty(): string
    {
        return 'max.poll.interval.ms';
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getDescription(): string
    {
        return sprintf(
            <<<EOT
            The maximum delay between invocations of poll() when using consumer group management. This places an upper
            bound on the amount of time that the consumer can be idle before fetching more records. If poll() is not
            called before expiration of this timeout, then the consumer is considered failed and the group will
            rebalance in order to reassign the partitions to another member. For consumers using a non-null
            group.instance.id which reach this timeout, partitions will not be immediately reassigned. Instead, the
            consumer will stop sending heartbeats and partitions will be reassigned after expiration of
            session.timeout.ms. This mirrors the behavior of a static consumer which has shutdown.
            Defaults to %s ms. Set 0 to disable.
            EOT,
            $this->getDefaultValue()
        );
    }

    public function isValueValid($value): bool
    {
        return is_numeric($value) && !str_contains((string)$value, '.') && $value >= 0;
    }

    public function getDefaultValue(): int
    {
        return 300000;
    }

    public function cast($validatedValue): int
    {
        return (int) $validatedValue;
    }
}
