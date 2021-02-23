<?php

declare(strict_types=1);

namespace Sts\KafkaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class StsKafkaBundle extends Bundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
