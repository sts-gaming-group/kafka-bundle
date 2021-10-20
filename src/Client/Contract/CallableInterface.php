<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Client\Contract;

interface CallableInterface
{
    public function callbacks(): array;
}
