<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Denormalizer;

use Sts\KafkaBundle\Denormalizer\Contract\DenormalizerInterface;

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
