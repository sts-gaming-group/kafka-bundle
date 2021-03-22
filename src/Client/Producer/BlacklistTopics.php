<?php

declare(strict_types=1);

namespace Sts\KafkaBundle\Client\Producer;

use Sts\KafkaBundle\Client\Contract\ProducerInterface;
use Sts\KafkaBundle\Configuration\ConfigurationResolver;
use Sts\KafkaBundle\Traits\CheckForRdKafkaExtensionTrait;

class BlacklistTopics
{
    public function getTopics(): array
    {
        // todo: fill in more topics or find a way to automate this
        $topics = [
            '%env%.dwh_kafka.tab_client_action_history',
            '%env%.dwh_kafka.tab_client_informations',
            '%env%.dwh_kafka.tab_client_liability',
            '%env%.dwh_kafka.tab_financial_operations',
            '%env%.dwh_kafka.tab_tickets_live',
            '%env%.dwh_kafka.tab_tickets_prematch',
            'sts_internal_%env%_tickets_prematch_agg',
            'sts_internal_%env%_tickets_live_agg'
        ];

        $result = [];
        foreach ($topics as $topic) {
            $result[] = str_replace('%env%', 'testing', $topic);
            $result[] = str_replace('%env%', 'production', $topic);
        }

        return $result;
    }
}
