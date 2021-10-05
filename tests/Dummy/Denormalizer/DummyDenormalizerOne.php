<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Tests\Dummy\Denormalizer;

use Sts\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;

class DummyDenormalizerOne implements DenormalizerInterface
{
    public function denormalize($data)
    {
        return $data;
    }
}
