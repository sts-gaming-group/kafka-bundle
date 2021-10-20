<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Contract;

interface CastValueInterface extends ConfigurationInterface
{
    /**
     * @param mixed $validatedValue
     * @return mixed
     */
    public function cast($validatedValue);
}
