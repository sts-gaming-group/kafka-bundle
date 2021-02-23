<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Contract;

interface ConfigurationInterface
{
    public function getName(): string;
    public function getMode(): int;
    public function getDescription(): string;
    /**
     * @return mixed
     */
    public function getDefaultValue();
}
