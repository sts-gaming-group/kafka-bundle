<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Command;

use Sts\KafkaBundle\Client\Consumer\ConsumerClient;
use Sts\KafkaBundle\Client\Consumer\ConsumerProvider;
use Sts\KafkaBundle\Client\Contract\ConsumerInterface;
use Sts\KafkaBundle\Command\Traits\DescribeTrait;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Configuration\RawConfiguration;
use Sts\KafkaBundle\Traits\AddConfigurationsToCommandTrait;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConsumeCommand extends Command
{
    use AddConfigurationsToCommandTrait;
    use DescribeTrait;

    protected static $defaultName = 'kafka:consumers:consume';

    private RawConfiguration $rawConfiguration;
    private ConsumerProvider $consumerProvider;
    private ConfigurationResolver $configurationResolver;
    private ConsumerClient $consumerClient;

    public function __construct(
        RawConfiguration $rawConfiguration,
        ConsumerProvider $consumerProvider,
        ConfigurationResolver $configurationResolver,
        ConsumerClient $consumerClient
    ) {
        $this->rawConfiguration = $rawConfiguration;
        $this->consumerProvider = $consumerProvider;
        $this->configurationResolver = $configurationResolver;
        $this->consumerClient = $consumerClient;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            sprintf(
                'Starts consuming messages from kafka using class implementing %s.',
                ConsumerInterface::class
            )
        )
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the registered consumer.')
            ->addOption('describe', null, InputOption::VALUE_NONE, 'Shows current consumer configuration');

        $this->addConfigurations($this->rawConfiguration);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consumer = $this->consumerProvider->provide($input->getArgument('name'));
        $configuration = $this->configurationResolver->resolve($consumer, $input);

        if ($input->getOption('describe')) {
            $this->describe($configuration, $output, $consumer);

            return 0;
        }
        $this->consumerClient->consume($consumer);

        return 0;
    }
}
