<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Denormalizer\Contract;

interface DenormalizerInterface
{
    /**
     * @param mixed $data
     * @return mixed
     */
    public function denormalize($data);
}
