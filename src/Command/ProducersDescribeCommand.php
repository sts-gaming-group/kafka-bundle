<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Command;

use StsGamingGroup\KafkaBundle\Client\Producer\ProducerProvider;
use StsGamingGroup\KafkaBundle\Command\Traits\DescribeTrait;
use StsGamingGroup\KafkaBundle\Configuration\ConfigurationResolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kafka:producers:describe',
    description: 'Show producers configuration.'
)]
class ProducersDescribeCommand extends Command
{
    use DescribeTrait;

    private ProducerProvider $producerProvider;
    private ConfigurationResolver $configurationResolver;

    public function __construct(
        ProducerProvider $producerProvider,
        ConfigurationResolver $configurationResolver
    ) {
        $this->producerProvider = $producerProvider;
        $this->configurationResolver = $configurationResolver;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $producers = $this->producerProvider->getProducers();

        foreach ($producers as $producer) {
            $this->describe($this->configurationResolver->resolve($producer), $output, $producer);
        }

        return self::SUCCESS;
    }
}
