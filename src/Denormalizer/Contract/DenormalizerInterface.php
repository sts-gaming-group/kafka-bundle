<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Denormalizer\Contract;

interface DenormalizerInterface
{
    /**
     * @param mixed $data
     * @return mixed
     */
    public function denormalize($data);
}
