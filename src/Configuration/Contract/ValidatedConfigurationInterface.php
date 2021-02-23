<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration\Contract;

use Sts\KafkaBundle\Configuration\ConfigurationContainer;

interface ValidatedConfigurationInterface extends ConfigurationInterface
{
    public function validate(ConfigurationContainer $configuration): bool;
    public function validationError(ConfigurationContainer $configuration): string;
}
