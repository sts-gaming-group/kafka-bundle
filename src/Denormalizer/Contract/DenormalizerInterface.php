<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Denormalizer\Contract;

interface DenormalizerInterface
{
    public function denormalize($data);

}
