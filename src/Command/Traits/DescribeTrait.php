<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Command\Traits;

use Sts\KafkaBundle\Client\Contract\ClientInterface;
use Sts\KafkaBundle\Configuration\ResolvedConfiguration;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

trait DescribeTrait
{
    public function describe(
        ResolvedConfiguration $resolvedConfiguration,
        OutputInterface $output,
        ClientInterface $client
    ): void {
        $table = new Table($output);
        $table->setHeaders(['configuration', 'value']);
        $table->setStyle('box');
        $values['class'] = get_class($client);

        foreach ($resolvedConfiguration->getConfigurations() as $name => $configuration) {
            $resolvedValue = $configuration['resolvedValue'];
            if (is_array($resolvedValue)) {
                $values[$name] = implode(', ', $resolvedValue);
            } elseif ($resolvedValue === true) {
                $values[$name] = 'true';
            } elseif ($resolvedValue === false) {
                $values[$name] = 'false';
            } else {
                $values[$name] = $resolvedValue;
            }
        }

        foreach ($values as $name => $value) {
            $table->addRow([$name, $value]);
        }

        $table->render();
    }
}
