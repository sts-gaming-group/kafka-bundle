<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Unit\Configuration\Type;

use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Configuration\Type\EnableAutoCommit;

class EnableAutoCommitTest extends AbstractBooleanConfigurationTest
{
    protected function getConfiguration(): ConfigurationInterface
    {
        return new EnableAutoCommit();
    }
}
