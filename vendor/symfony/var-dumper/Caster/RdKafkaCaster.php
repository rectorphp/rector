<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20211020\Symfony\Component\VarDumper\Caster;

use RdKafka\Conf;
use RdKafka\Exception as RdKafkaException;
use RdKafka\KafkaConsumer;
use RdKafka\Message;
use RectorPrefix20211020\RdKafka\Metadata\Broker as BrokerMetadata;
use RdKafka\Metadata\Collection as CollectionMetadata;
use RdKafka\Metadata\Partition as PartitionMetadata;
use RdKafka\Metadata\Topic as TopicMetadata;
use RdKafka\Topic;
use RdKafka\TopicConf;
use RdKafka\TopicPartition;
use RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub;
/**
 * Casts RdKafka related classes to array representation.
 *
 * @author Romain Neutron <imprec@gmail.com>
 */
class RdKafkaCaster
{
    public static function castKafkaConsumer(\RdKafka\KafkaConsumer $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        try {
            $assignment = $c->getAssignment();
        } catch (\RdKafka\Exception $e) {
            $assignment = [];
        }
        $a += [$prefix . 'subscription' => $c->getSubscription(), $prefix . 'assignment' => $assignment];
        $a += self::extractMetadata($c);
        return $a;
    }
    public static function castTopic(\RdKafka\Topic $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        $a += [$prefix . 'name' => $c->getName()];
        return $a;
    }
    public static function castTopicPartition(\RdKafka\TopicPartition $c, array $a)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        $a += [$prefix . 'offset' => $c->getOffset(), $prefix . 'partition' => $c->getPartition(), $prefix . 'topic' => $c->getTopic()];
        return $a;
    }
    public static function castMessage(\RdKafka\Message $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        $a += [$prefix . 'errstr' => $c->errstr()];
        return $a;
    }
    public static function castConf(\RdKafka\Conf $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        foreach ($c->dump() as $key => $value) {
            $a[$prefix . $key] = $value;
        }
        return $a;
    }
    public static function castTopicConf(\RdKafka\TopicConf $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        foreach ($c->dump() as $key => $value) {
            $a[$prefix . $key] = $value;
        }
        return $a;
    }
    public static function castRdKafka(\RdKafka $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        $a += [$prefix . 'out_q_len' => $c->getOutQLen()];
        $a += self::extractMetadata($c);
        return $a;
    }
    public static function castCollectionMetadata(\RdKafka\Metadata\Collection $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $a += \iterator_to_array($c);
        return $a;
    }
    public static function castTopicMetadata(\RdKafka\Metadata\Topic $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        $a += [$prefix . 'name' => $c->getTopic(), $prefix . 'partitions' => $c->getPartitions()];
        return $a;
    }
    public static function castPartitionMetadata(\RdKafka\Metadata\Partition $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        $a += [$prefix . 'id' => $c->getId(), $prefix . 'err' => $c->getErr(), $prefix . 'leader' => $c->getLeader()];
        return $a;
    }
    public static function castBrokerMetadata(\RectorPrefix20211020\RdKafka\Metadata\Broker $c, array $a, \RectorPrefix20211020\Symfony\Component\VarDumper\Cloner\Stub $stub, bool $isNested)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        $a += [$prefix . 'id' => $c->getId(), $prefix . 'host' => $c->getHost(), $prefix . 'port' => $c->getPort()];
        return $a;
    }
    private static function extractMetadata($c)
    {
        $prefix = \RectorPrefix20211020\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL;
        try {
            $m = $c->getMetadata(\true, null, 500);
        } catch (\RdKafka\Exception $e) {
            return [];
        }
        return [$prefix . 'orig_broker_id' => $m->getOrigBrokerId(), $prefix . 'orig_broker_name' => $m->getOrigBrokerName(), $prefix . 'brokers' => $m->getBrokers(), $prefix . 'topics' => $m->getTopics()];
    }
}
