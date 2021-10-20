<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Denormalizer;

use StsGamingGroup\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;

class PlainDenormalizer implements DenormalizerInterface
{
    /**
     * @param mixed $data
     * @return mixed
     */
    public function denormalize($data)
    {
        return $data;
    }
}
