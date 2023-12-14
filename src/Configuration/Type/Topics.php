<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Type;

use StsGamingGroup\KafkaBundle\Configuration\Contract\ConsumerConfigurationInterface;
use StsGamingGroup\KafkaBundle\Configuration\Traits\SupportsConsumerTrait;
use Symfony\Component\Console\Input\InputOption;

class Topics implements ConsumerConfigurationInterface
{
    use SupportsConsumerTrait;

    public const NAME = 'topics';

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
        return 'Consumer topics to read messages from. Defaults to empty array - must be chosen explicitly.';
    }

    public function isValueValid($value): bool
    {
        if (!is_array($value) || empty($value)) {
            return false;
        }

        foreach ($value as $topic) {
            if (!is_string($topic) || '' === $topic) {
                return false;
            }
        }

        return true;
    }

    public function getDefaultValue(): array
    {
        return ['topic_1', 'topic_2'];
    }
}
