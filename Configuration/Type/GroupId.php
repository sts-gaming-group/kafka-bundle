<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class GroupId implements ConfigurationInterface
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

    public function getDescription(): string
    {
        return 'Client group id string. All clients sharing the same group.id belong to the same group. 
        Defaults to empty string - must be chosen explicitly.';
    }

    public function getDefaultValue(): string
    {
        return '';
    }
}
