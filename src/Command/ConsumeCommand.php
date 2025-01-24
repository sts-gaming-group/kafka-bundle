<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Command;

use StsGamingGroup\KafkaBundle\Client\Consumer\ConsumerClient;
use StsGamingGroup\KafkaBundle\Client\Consumer\ConsumerProvider;
use StsGamingGroup\KafkaBundle\Client\Contract\ConsumerInterface;
use StsGamingGroup\KafkaBundle\Command\Traits\DescribeTrait;
use StsGamingGroup\KafkaBundle\Configuration\ConfigurationResolver;
use StsGamingGroup\KafkaBundle\Configuration\RawConfiguration;
use StsGamingGroup\KafkaBundle\Traits\AddConfigurationsToCommandTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'kafka:consumers:consume',
    description: 'Starts consuming messages from kafka using class implementing '.ConsumerInterface::class
)]
class ConsumeCommand extends Command
{
    use AddConfigurationsToCommandTrait;
    use DescribeTrait;

    private RawConfiguration $rawConfiguration;
    private ConsumerProvider $consumerProvider;
    private ConsumerClient $consumerClient;
    private ConfigurationResolver $configurationResolver;

    public function __construct(
        RawConfiguration $rawConfiguration,
        ConsumerProvider $consumerProvider,
        ConsumerClient $consumerClient,
        ConfigurationResolver $configurationResolver
    ) {
        $this->rawConfiguration = $rawConfiguration;
        $this->consumerProvider = $consumerProvider;
        $this->consumerClient = $consumerClient;
        $this->configurationResolver = $configurationResolver;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(name: 'name', mode: InputArgument::REQUIRED, description: 'Name of the registered consumer.')
            ->addOption(name: 'describe', mode: InputOption::VALUE_NONE, description: 'Describes consumer');

        $this->addConfigurations($this->rawConfiguration);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumer = $this->consumerProvider->provide($input->getArgument('name'));

        if ($input->getOption('describe')) {
            $this->describe($this->configurationResolver->resolve($consumer, $input), $output, $consumer);

            return self::SUCCESS;
        }

        $this->consumerClient->consume($consumer, $input);

        return self::SUCCESS;
    }
}
