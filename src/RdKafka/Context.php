<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\RdKafka;

use Sts\KafkaBundle\Configuration\ResolvedConfiguration;

class Context
{
    private ResolvedConfiguration $configuration;
    private int $retryNo;

    public function __construct(ResolvedConfiguration $configuration, int $retryNo)
    {
        $this->configuration = $configuration;
        $this->retryNo = $retryNo;
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getValue(string $name)
    {
        return $this->configuration->getValue($name);
    }

    public function getRetryNo(): int
    {
        return $this->retryNo;
    }
}
