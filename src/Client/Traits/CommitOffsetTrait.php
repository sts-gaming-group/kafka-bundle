<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\Client\Traits;

use RdKafka\Exception;
use StsGamingGroup\KafkaBundle\Configuration\Type\EnableAutoCommit;
use StsGamingGroup\KafkaBundle\Configuration\Exception\InvalidConfigurationException;
use StsGamingGroup\KafkaBundle\RdKafka\Context;

trait CommitOffsetTrait
{
    /**
     * @param Context $context
     * @param bool $async
     * @return bool
     * @throws Exception
     */
    public function commitOffset(Context $context, bool $async = false): bool
    {
        if ($context->getValue(EnableAutoCommit::NAME) === 'true') {
            throw new InvalidConfigurationException(sprintf(
                'Unable to manually commit offset when %s configuration is set to `true`.',
                EnableAutoCommit::NAME
            ));
        }

        if ($async) {
            $context->getRdKafkaConsumer()->commitAsync($context->getRdKafkaMessage());
        } else {
            $context->getRdKafkaConsumer()->commit($context->getRdKafkaMessage());
        }

        return true;
    }
}
