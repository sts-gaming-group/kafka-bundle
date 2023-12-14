<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Configuration\Contract;

use StsGamingGroup\KafkaBundle\Client\Contract\ClientInterface;

interface ConfigurationInterface
{
    public function getName(): string;
    public function getMode(): int;
    public function getDescription(): string;
    /**
     * @param mixed $value
     * @return bool
     */
    public function isValueValid($value): bool;

    /**
     * @return mixed
     */
    public function getDefaultValue();

    public function supportsClient(ClientInterface $client): bool;
}
