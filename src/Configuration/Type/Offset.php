<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Symfony\Component\Console\Input\InputOption;

class Offset implements ConfigurationInterface
{
    public const NAME = 'offset';

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
        return 'Offset from which begin consumption in given topic. Defaults to RD_KAFKA_OFFSET_STORED (-1000)';
    }

    public function getDefaultValue(): int
    {
        return RD_KAFKA_OFFSET_STORED;
    }
}
