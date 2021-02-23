<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Decoder;

use Sts\KafkaBundle\Configuration\ConfigurationContainer;
use Sts\KafkaBundle\Configuration\Type\SchemaRegistry;
use Sts\KafkaBundle\Decoder\Contract\DecoderInterface;
use FlixTech\SchemaRegistryApi\Registry\Cache\AvroObjectCacheAdapter;
use FlixTech\SchemaRegistryApi\Registry\CachedRegistry;
use FlixTech\SchemaRegistryApi\Registry\PromisingRegistry;
use FlixTech\AvroSerializer\Objects\RecordSerializer;
use GuzzleHttp\Client;

class AvroDecoder implements DecoderInterface
{
    private ?CachedRegistry $cachedRegistry = null;
    private ?RecordSerializer $recordSerializer = null;

    public function decode(ConfigurationContainer $configuration, string $message): array
    {
        if (!$this->cachedRegistry) {
            $client = new Client(['base_uri' => $configuration->getConfiguration(SchemaRegistry::NAME)]);
            $this->cachedRegistry = new CachedRegistry(
                new PromisingRegistry($client),
                new AvroObjectCacheAdapter()
            );
        }

        if (!$this->recordSerializer) {
            $this->recordSerializer = new RecordSerializer(
                $this->cachedRegistry,
                [
                    RecordSerializer::OPTION_REGISTER_MISSING_SCHEMAS => false,
                    RecordSerializer::OPTION_REGISTER_MISSING_SUBJECTS => false,
                ]
            );
        }

        return $this->recordSerializer->decodeMessage($message);
    }
}
