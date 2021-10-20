<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class StsGamingGroupKafkaBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
