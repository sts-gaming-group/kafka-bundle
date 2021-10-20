<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ProducerConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class ProducerPartition implements ProducerConfigurationInterface
{
    public const NAME = 'producer_partition';

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
        return
            <<<EOT
            Which partition producer should produce to. 
            Defaults to RD_KAFKA_PARTITION_UA (-1) and lets librdkafka choose the partition according to message key value.
            EOT;
    }

    public function isValueValid($value): bool
    {
        return (is_numeric($value) && strpos((string) $value, '.') === false && $value >= 0) ||
            $value === $this->getDefaultValue();
    }

    public function getDefaultValue(): int
    {
        return defined('RD_KAFKA_PARTITION_UA') ? RD_KAFKA_PARTITION_UA : -1;
    }
}
