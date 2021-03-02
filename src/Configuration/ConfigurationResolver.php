<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Configuration;

use Sts\KafkaBundle\Configuration\Contract\CastValueInterface;
use Sts\KafkaBundle\Configuration\Contract\ConfigurationInterface;
use Sts\KafkaBundle\Consumer\Contract\ConsumerInterface;
use Sts\KafkaBundle\Exception\InvalidConfigurationException;
use Sts\KafkaBundle\Producer\Contract\ProducerInterface;
use Symfony\Component\Console\Input\InputInterface;

class ConfigurationResolver
{
    private RawConfiguration $rawConfiguration;
    private array $yamlConfig;

    public function __construct(RawConfiguration $rawConfiguration, array $yamlConfig)
    {
        $this->rawConfiguration = $rawConfiguration;
        $this->yamlConfig = $yamlConfig;
    }

    public function resolveForConsumer(ConsumerInterface $consumer, InputInterface $input): ResolvedConfiguration
    {
        return $this->doResolve($consumer, $input);
    }

    public function resolveForProducer(ProducerInterface $producer): ResolvedConfiguration
    {
        return $this->doResolve($producer);
    }

    /**
     * @param ConsumerInterface|ProducerInterface $consumerOrProducer
     * @param InputInterface|null $input
     * @return ResolvedConfiguration
     */
    private function doResolve($consumerOrProducer, ?InputInterface $input = null): ResolvedConfiguration
    {
        $resolvedConfiguration = new ResolvedConfiguration();

        foreach ($this->rawConfiguration->getConfigurations() as $configuration) {
            $resolvedValue = $this->getResolvedValue($configuration, $consumerOrProducer, $input);
            if ($configuration instanceof CastValueInterface) {
                $resolvedValue = $configuration->cast($resolvedValue);
            }
            $resolvedConfiguration->addConfiguration($configuration, $resolvedValue);
        }

        return $resolvedConfiguration;
    }

    /**
     * @param ConfigurationInterface $configuration
     * @param ConsumerInterface|ProducerInterface $consumerOrProducer
     * @param InputInterface|null $input
     * @return mixed
     */
    private function getResolvedValue(
        ConfigurationInterface $configuration,
        $consumerOrProducer,
        ?InputInterface $input
    ) {
        $name = $configuration->getName();
        $className = get_class($consumerOrProducer);

        if ($input && $input->getParameterOption('--' . $name) !== false) {
            $value = $input->getOption($name);
            if (!$configuration->isValueValid($value)) {
                throw new InvalidConfigurationException(sprintf(
                    'Invalid option passed for %s. Passed value `%s`. Configuration description: %s',
                    $name,
                    is_array($value) ? implode(', ', $value) : $value,
                    $configuration->getDescription()
                ));
            }

            return $value;
        }

        if ($consumerOrProducer instanceof ConsumerInterface) {
            $configName = 'consumers';
        } elseif ($consumerOrProducer instanceof ProducerInterface) {
            $configName = 'producers';
        } else {
            throw new \RuntimeException(sprintf(
                'Object must implement %s or %s to properly resolve configuration.',
                ConsumerInterface::class,
                ProducerInterface::class
            ));
        }

        if (isset($this->yamlConfig[$configName][$className][$name])) {
            return $this->yamlConfig[$configName][$className][$name];
        }

        if (isset($this->yamlConfig[$name])) {
            return $this->yamlConfig[$name];
        }

        throw new InvalidConfigurationException(
            sprintf(
                'Set `%s` configuration either in global config, consumer definition or pass it as an option in CLI.',
                $name
            )
        );
    }
}
