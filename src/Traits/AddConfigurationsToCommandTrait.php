<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Traits;

use StsGamingGroup\KafkaBundle\Configuration\RawConfiguration;
use Symfony\Component\Console\Command\Command;

trait AddConfigurationsToCommandTrait
{
    public function addConfigurations(RawConfiguration $rawConfiguration): void
    {
        if (!is_subclass_of(static::class, Command::class)) {
            throw new \RuntimeException(sprintf(
                'Unable to use %s outside %s',
                __TRAIT__,
                Command::class
            ));
        }

        foreach ($rawConfiguration->getConfigurations() as $configuration) {
            $this->addOption(
                $configuration->getName(),
                null,
                $configuration->getMode(),
                $configuration->getDescription()
            );
        }
    }
}
