<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Command\Traits;

use StsGamingGroup\KafkaBundle\Client\Contract\ClientInterface;
use StsGamingGroup\KafkaBundle\Client\Contract\ConsumerInterface;
use StsGamingGroup\KafkaBundle\Client\Contract\ProducerInterface;
use StsGamingGroup\KafkaBundle\Configuration\ResolvedConfiguration;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

trait DescribeTrait
{
    public function describe(
        ResolvedConfiguration $configuration,
        OutputInterface $output,
        ClientInterface $client
    ): void {
        $table = new Table($output);
        $table->setHeaders(['configuration', 'value']);
        $table->setStyle('box');
        $values['class'] = get_class($client);

        $configurationType = ResolvedConfiguration::ALL_TYPES;
        if ($client instanceof ConsumerInterface) {
            $values['name'] = $client->getName();
            $configurationType = ResolvedConfiguration::CONSUMER_TYPES;
        }

        if ($client instanceof ProducerInterface) {
            $configurationType = ResolvedConfiguration::PRODUCER_TYPES;
        }

        foreach ($configuration->getConfigurations($configurationType) as $configuration) {
            $resolvedValue = $configuration['resolvedValue'];
            $name = $configuration['configuration']->getName();
            if (is_array($resolvedValue)) {
                $values[$name] = implode(PHP_EOL, $resolvedValue);

                continue;
            }

            if ($resolvedValue === true || $resolvedValue === false) {
                $values[$name] = var_export($resolvedValue, true);

                continue;
            }
            $values[$name] = $resolvedValue;
        }

        foreach ($values as $name => $value) {
            $table->addRow([$name, $value]);
        }

        $table->render();
    }
}
