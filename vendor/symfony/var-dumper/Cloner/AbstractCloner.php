<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace RectorPrefix20210827\Symfony\Component\VarDumper\Cloner;

use RectorPrefix20210827\Symfony\Component\VarDumper\Caster\Caster;
use RectorPrefix20210827\Symfony\Component\VarDumper\Exception\ThrowingCasterException;
/**
 * AbstractCloner implements a generic caster mechanism for objects and resources.
 *
 * @author Nicolas Grekas <p@tchwork.com>
 */
abstract class AbstractCloner implements \RectorPrefix20210827\Symfony\Component\VarDumper\Cloner\ClonerInterface
{
    public static $defaultCasters = ['__PHP_Incomplete_Class' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\Caster', 'castPhpIncompleteClass'], 'RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\CutStub' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castStub'], 'RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\CutArrayStub' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castCutArray'], 'RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ConstStub' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castStub'], 'RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\EnumStub' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'castEnum'], 'Closure' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castClosure'], 'Generator' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castGenerator'], 'ReflectionType' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castType'], 'ReflectionAttribute' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castAttribute'], 'ReflectionGenerator' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castReflectionGenerator'], 'ReflectionClass' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castClass'], 'ReflectionClassConstant' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castClassConstant'], 'ReflectionFunctionAbstract' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castFunctionAbstract'], 'ReflectionMethod' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castMethod'], 'ReflectionParameter' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castParameter'], 'ReflectionProperty' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castProperty'], 'ReflectionReference' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castReference'], 'ReflectionExtension' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castExtension'], 'ReflectionZendExtension' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ReflectionCaster', 'castZendExtension'], 'RectorPrefix20210827\\Doctrine\\Common\\Persistence\\ObjectManager' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RectorPrefix20210827\\Doctrine\\Common\\Proxy\\Proxy' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DoctrineCaster', 'castCommonProxy'], 'RectorPrefix20210827\\Doctrine\\ORM\\Proxy\\Proxy' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DoctrineCaster', 'castOrmProxy'], 'RectorPrefix20210827\\Doctrine\\ORM\\PersistentCollection' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DoctrineCaster', 'castPersistentCollection'], 'RectorPrefix20210827\\Doctrine\\Persistence\\ObjectManager' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'DOMException' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castException'], 'DOMStringList' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMNameList' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMImplementation' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castImplementation'], 'DOMImplementationList' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMNode' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castNode'], 'DOMNameSpaceNode' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castNameSpaceNode'], 'DOMDocument' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castDocument'], 'DOMNodeList' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMNamedNodeMap' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLength'], 'DOMCharacterData' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castCharacterData'], 'DOMAttr' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castAttr'], 'DOMElement' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castElement'], 'DOMText' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castText'], 'DOMTypeinfo' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castTypeinfo'], 'DOMDomError' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castDomError'], 'DOMLocator' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castLocator'], 'DOMDocumentType' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castDocumentType'], 'DOMNotation' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castNotation'], 'DOMEntity' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castEntity'], 'DOMProcessingInstruction' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castProcessingInstruction'], 'DOMXPath' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DOMCaster', 'castXPath'], 'XMLReader' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\XmlReaderCaster', 'castXmlReader'], 'ErrorException' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castErrorException'], 'Exception' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castException'], 'Error' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castError'], 'RectorPrefix20210827\\Symfony\\Bridge\\Monolog\\Logger' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RectorPrefix20210827\\Symfony\\Component\\DependencyInjection\\ContainerInterface' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RectorPrefix20210827\\Symfony\\Component\\EventDispatcher\\EventDispatcherInterface' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RectorPrefix20210827\\Symfony\\Component\\HttpClient\\CurlHttpClient' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClient'], 'RectorPrefix20210827\\Symfony\\Component\\HttpClient\\NativeHttpClient' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClient'], 'RectorPrefix20210827\\Symfony\\Component\\HttpClient\\Response\\CurlResponse' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClientResponse'], 'RectorPrefix20210827\\Symfony\\Component\\HttpClient\\Response\\NativeResponse' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castHttpClientResponse'], 'RectorPrefix20210827\\Symfony\\Component\\HttpFoundation\\Request' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SymfonyCaster', 'castRequest'], 'RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Exception\\ThrowingCasterException' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castThrowingCasterException'], 'RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\TraceStub' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castTraceStub'], 'RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\FrameStub' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castFrameStub'], 'RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Cloner\\AbstractCloner' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RectorPrefix20210827\\Symfony\\Component\\ErrorHandler\\Exception\\SilencedErrorContext' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ExceptionCaster', 'castSilencedErrorContext'], 'RectorPrefix20210827\\Imagine\\Image\\ImageInterface' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ImagineCaster', 'castImage'], 'RectorPrefix20210827\\Ramsey\\Uuid\\UuidInterface' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\UuidCaster', 'castRamseyUuid'], 'RectorPrefix20210827\\ProxyManager\\Proxy\\ProxyInterface' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ProxyManagerCaster', 'castProxy'], 'PHPUnit_Framework_MockObject_MockObject' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RectorPrefix20210827\\PHPUnit\\Framework\\MockObject\\MockObject' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RectorPrefix20210827\\PHPUnit\\Framework\\MockObject\\Stub' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RectorPrefix20210827\\Prophecy\\Prophecy\\ProphecySubjectInterface' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'RectorPrefix20210827\\Mockery\\MockInterface' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\StubCaster', 'cutInternals'], 'PDO' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\PdoCaster', 'castPdo'], 'PDOStatement' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\PdoCaster', 'castPdoStatement'], 'AMQPConnection' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castConnection'], 'AMQPChannel' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castChannel'], 'AMQPQueue' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castQueue'], 'AMQPExchange' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castExchange'], 'AMQPEnvelope' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\AmqpCaster', 'castEnvelope'], 'ArrayObject' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castArrayObject'], 'ArrayIterator' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castArrayIterator'], 'SplDoublyLinkedList' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castDoublyLinkedList'], 'SplFileInfo' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castFileInfo'], 'SplFileObject' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castFileObject'], 'SplHeap' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castHeap'], 'SplObjectStorage' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castObjectStorage'], 'SplPriorityQueue' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castHeap'], 'OuterIterator' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castOuterIterator'], 'WeakReference' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\SplCaster', 'castWeakReference'], 'Redis' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RedisCaster', 'castRedis'], 'RedisArray' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RedisCaster', 'castRedisArray'], 'RedisCluster' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RedisCaster', 'castRedisCluster'], 'DateTimeInterface' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castDateTime'], 'DateInterval' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castInterval'], 'DateTimeZone' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castTimeZone'], 'DatePeriod' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DateCaster', 'castPeriod'], 'GMP' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\GmpCaster', 'castGmp'], 'MessageFormatter' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castMessageFormatter'], 'NumberFormatter' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castNumberFormatter'], 'IntlTimeZone' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castIntlTimeZone'], 'IntlCalendar' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castIntlCalendar'], 'IntlDateFormatter' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\IntlCaster', 'castIntlDateFormatter'], 'Memcached' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\MemcachedCaster', 'castMemcached'], 'RectorPrefix20210827\\Ds\\Collection' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castCollection'], 'RectorPrefix20210827\\Ds\\Map' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castMap'], 'RectorPrefix20210827\\Ds\\Pair' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castPair'], 'RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DsPairStub' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\DsCaster', 'castPairStub'], 'CurlHandle' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castCurl'], ':curl' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castCurl'], ':dba' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castDba'], ':dba persistent' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castDba'], 'GdImage' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castGd'], ':gd' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castGd'], ':mysql link' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castMysqlLink'], ':pgsql large object' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castLargeObject'], ':pgsql link' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castLink'], ':pgsql link persistent' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castLink'], ':pgsql result' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\PgSqlCaster', 'castResult'], ':process' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castProcess'], ':stream' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castStream'], 'OpenSSLCertificate' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castOpensslX509'], ':OpenSSL X.509' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castOpensslX509'], ':persistent stream' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castStream'], ':stream-context' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\ResourceCaster', 'castStreamContext'], 'XmlParser' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\XmlResourceCaster', 'castXml'], ':xml' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\XmlResourceCaster', 'castXml'], 'RdKafka' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castRdKafka'], 'RectorPrefix20210827\\RdKafka\\Conf' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castConf'], 'RectorPrefix20210827\\RdKafka\\KafkaConsumer' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castKafkaConsumer'], 'RectorPrefix20210827\\RdKafka\\Metadata\\Broker' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castBrokerMetadata'], 'RectorPrefix20210827\\RdKafka\\Metadata\\Collection' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castCollectionMetadata'], 'RectorPrefix20210827\\RdKafka\\Metadata\\Partition' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castPartitionMetadata'], 'RectorPrefix20210827\\RdKafka\\Metadata\\Topic' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopicMetadata'], 'RectorPrefix20210827\\RdKafka\\Message' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castMessage'], 'RectorPrefix20210827\\RdKafka\\Topic' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopic'], 'RectorPrefix20210827\\RdKafka\\TopicPartition' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopicPartition'], 'RectorPrefix20210827\\RdKafka\\TopicConf' => ['RectorPrefix20210827\\Symfony\\Component\\VarDumper\\Caster\\RdKafkaCaster', 'castTopicConf']];
    protected $maxItems = 2500;
    protected $maxString = -1;
    protected $minDepth = 1;
    private $casters = [];
    private $prevErrorHandler;
    private $classInfo = [];
    private $filter = 0;
    /**
     * @param callable[]|null $casters A map of casters
     *
     * @see addCasters
     */
    public function __construct(array $casters = null)
    {
        if (null === $casters) {
            $casters = static::$defaultCasters;
        }
        $this->addCasters($casters);
    }
    /**
     * Adds casters for resources and objects.
     *
     * Maps resources or objects types to a callback.
     * Types are in the key, with a callable caster for value.
     * Resource types are to be prefixed with a `:`,
     * see e.g. static::$defaultCasters.
     *
     * @param callable[] $casters A map of casters
     */
    public function addCasters($casters)
    {
        foreach ($casters as $type => $callback) {
            $this->casters[$type][] = $callback;
        }
    }
    /**
     * Sets the maximum number of items to clone past the minimum depth in nested structures.
     * @param int $maxItems
     */
    public function setMaxItems($maxItems)
    {
        $this->maxItems = $maxItems;
    }
    /**
     * Sets the maximum cloned length for strings.
     * @param int $maxString
     */
    public function setMaxString($maxString)
    {
        $this->maxString = $maxString;
    }
    /**
     * Sets the minimum tree depth where we are guaranteed to clone all the items.  After this
     * depth is reached, only setMaxItems items will be cloned.
     * @param int $minDepth
     */
    public function setMinDepth($minDepth)
    {
        $this->minDepth = $minDepth;
    }
    /**
     * Clones a PHP variable.
     *
     * @param mixed $var    Any PHP variable
     * @param int   $filter A bit field of Caster::EXCLUDE_* constants
     *
     * @return Data The cloned variable represented by a Data object
     */
    public function cloneVar($var, $filter = 0)
    {
        $this->prevErrorHandler = \set_error_handler(function ($type, $msg, $file, $line, $context = []) {
            if (\E_RECOVERABLE_ERROR === $type || \E_USER_ERROR === $type) {
                // Cloner never dies
                throw new \ErrorException($msg, 0, $type, $file, $line);
            }
            if ($this->prevErrorHandler) {
                return ($this->prevErrorHandler)($type, $msg, $file, $line, $context);
            }
            return \false;
        });
        $this->filter = $filter;
        if ($gc = \gc_enabled()) {
            \gc_disable();
        }
        try {
            return new \RectorPrefix20210827\Symfony\Component\VarDumper\Cloner\Data($this->doClone($var));
        } finally {
            if ($gc) {
                \gc_enable();
            }
            \restore_error_handler();
            $this->prevErrorHandler = null;
        }
    }
    /**
     * Effectively clones the PHP variable.
     *
     * @param mixed $var Any PHP variable
     *
     * @return array The cloned variable represented in an array
     */
    protected abstract function doClone($var);
    /**
     * Casts an object to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array The object casted as array
     * @param \Symfony\Component\VarDumper\Cloner\Stub $stub
     */
    protected function castObject($stub, $isNested)
    {
        $obj = $stub->value;
        $class = $stub->class;
        if (\PHP_VERSION_ID < 80000 ? "\0" === ($class[15] ?? null) : \strpos($class, "@anonymous\0") !== \false) {
            $stub->class = \get_debug_type($obj);
        }
        if (isset($this->classInfo[$class])) {
            [$i, $parents, $hasDebugInfo, $fileInfo] = $this->classInfo[$class];
        } else {
            $i = 2;
            $parents = [$class];
            $hasDebugInfo = \method_exists($class, '__debugInfo');
            foreach (\class_parents($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            foreach (\class_implements($class) as $p) {
                $parents[] = $p;
                ++$i;
            }
            $parents[] = '*';
            $r = new \ReflectionClass($class);
            $fileInfo = $r->isInternal() || $r->isSubclassOf(\RectorPrefix20210827\Symfony\Component\VarDumper\Cloner\Stub::class) ? [] : ['file' => $r->getFileName(), 'line' => $r->getStartLine()];
            $this->classInfo[$class] = [$i, $parents, $hasDebugInfo, $fileInfo];
        }
        $stub->attr += $fileInfo;
        $a = \RectorPrefix20210827\Symfony\Component\VarDumper\Caster\Caster::castObject($obj, $class, $hasDebugInfo, $stub->class);
        try {
            while ($i--) {
                if (!empty($this->casters[$p = $parents[$i]])) {
                    foreach ($this->casters[$p] as $callback) {
                        $a = $callback($obj, $a, $stub, $isNested, $this->filter);
                    }
                }
            }
        } catch (\Exception $e) {
            $a = [(\RectorPrefix20210827\Symfony\Component\VarDumper\Cloner\Stub::TYPE_OBJECT === $stub->type ? \RectorPrefix20210827\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL : '') . '⚠' => new \RectorPrefix20210827\Symfony\Component\VarDumper\Exception\ThrowingCasterException($e)] + $a;
        }
        return $a;
    }
    /**
     * Casts a resource to an array representation.
     *
     * @param bool $isNested True if the object is nested in the dumped structure
     *
     * @return array The resource casted as array
     * @param \Symfony\Component\VarDumper\Cloner\Stub $stub
     */
    protected function castResource($stub, $isNested)
    {
        $a = [];
        $res = $stub->value;
        $type = $stub->class;
        try {
            if (!empty($this->casters[':' . $type])) {
                foreach ($this->casters[':' . $type] as $callback) {
                    $a = $callback($res, $a, $stub, $isNested, $this->filter);
                }
            }
        } catch (\Exception $e) {
            $a = [(\RectorPrefix20210827\Symfony\Component\VarDumper\Cloner\Stub::TYPE_OBJECT === $stub->type ? \RectorPrefix20210827\Symfony\Component\VarDumper\Caster\Caster::PREFIX_VIRTUAL : '') . '⚠' => new \RectorPrefix20210827\Symfony\Component\VarDumper\Exception\ThrowingCasterException($e)] + $a;
        }
        return $a;
    }
}
