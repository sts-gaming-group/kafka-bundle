<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\GlobalConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class GroupId implements GlobalConfigurationInterface
{
    public const NAME = 'group_id';

    public function getName(): string
    {
        return self::NAME;
    }

    public function getMode(): int
    {
        return InputOption::VALUE_REQUIRED;
    }

    public function getKafkaProperty(): string
    {
        return 'group.id';
    }

    public function getDescription(): string
    {
        return <<<EOT
        Client group id string. All clients sharing the same group.id belong to the same group. 
        Must be a non empty string
        EOT;
    }

    public function isValueValid($value): bool
    {
        return is_string($value) && '' !== $value;
    }

    public static function getDefaultValue(): string
    {
        return 'sts_kafka';
    }
}
