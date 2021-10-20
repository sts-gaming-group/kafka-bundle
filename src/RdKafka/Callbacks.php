<?php

declare(strict_types=1);

namespace StsGamingGroup\KafkaBundle\RdKafka;

/**
 * @method setErrorCb(\RdKafka\KafkaConsumer $kafkaConsumer, int $error, array $partitions)
 * @method setLogCb(\RdKafka\KafkaConsumer $kafkaConsumer, int $level, string $facility, string $message)
 * @method setConsumeCb(\RdKafka\Message $message)
 * @method setDrMsgCb(\RdKafka\Producer $kafkaProducer, \RdKafka\Message $message)
 * @method setOffsetCommitCb(\RdKafka\KafkaConsumer $kafkaConsumer, int $error, array $partitions)
 * @method setRebalanceCb(\RdKafka\KafkaConsumer $kafkaConsumer, int $error, array $partitions)
 * @method setStatsCb($kafka, string $json, int $jsonLength) unable to check this callback
 */
class Callbacks
{
    public const ERROR_CALLBACK = 'setErrorCb';
    public const LOG_CALLBACK = 'setLogCb';
    public const CONSUME_CALLBACK = 'setConsumeCb';
    public const MESSAGE_DELIVERY_CALLBACK = 'setDrMsgCb';
    public const OFFSET_COMMIT_CALLBACK = 'setOffsetCommitCb';
    public const REBALANCE_CALLBACK = 'setRebalanceCb';
    public const STATISTICS_CALLBACK = 'setStatsCb';
}
