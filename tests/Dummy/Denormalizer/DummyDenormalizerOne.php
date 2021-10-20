<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Tests\Dummy\Denormalizer;

use StsGamingGroup\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;

class DummyDenormalizerOne implements DenormalizerInterface
{
    public function denormalize($data)
    {
        return $data;
    }
}
