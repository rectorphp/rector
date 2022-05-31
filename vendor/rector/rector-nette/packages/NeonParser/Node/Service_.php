<?php

declare (strict_types=1);
namespace Rector\Nette\NeonParser\Node;

use RectorPrefix20220531\Nette\Neon\Node;
use RectorPrefix20220531\Nette\Neon\Node\LiteralNode;
use Rector\Nette\NeonParser\Node\Service_\SetupMethodCall;
/**
 * Metanode for easier subscribing
 */
final class Service_ extends \Rector\Nette\NeonParser\Node\AbstractVirtualNode
{
    /**
     * @var string
     */
    private const UNKNOWN_TYPE = '__UNKNOWN_TYPE__';
    /**
     * @var string
     */
    private $className;
    /**
     * @var \Nette\Neon\Node\LiteralNode|null
     */
    private $classLiteralNode;
    /**
     * @var \Nette\Neon\Node\LiteralNode|null
     */
    private $factoryLiteralNode;
    /**
     * @var SetupMethodCall[]
     */
    private $setupMethodCalls;
    /**
     * @param SetupMethodCall[] $setupMethodCalls
     * @param \Nette\Neon\Node\LiteralNode|null $classLiteralNode
     * @param \Nette\Neon\Node\LiteralNode|null $factoryLiteralNode
     */
    public function __construct(string $className, $classLiteralNode, $factoryLiteralNode, array $setupMethodCalls)
    {
        $this->className = $className;
        $this->classLiteralNode = $classLiteralNode;
        $this->factoryLiteralNode = $factoryLiteralNode;
        $this->setupMethodCalls = $setupMethodCalls;
    }
    public function getClassName() : string
    {
        return $this->className;
    }
    public function getServiceType() : string
    {
        if ($this->classLiteralNode) {
            return $this->classLiteralNode->toString();
        }
        if ($this->factoryLiteralNode) {
            return $this->factoryLiteralNode->toString();
        }
        return self::UNKNOWN_TYPE;
    }
    /**
     * @return Node[]
     */
    public function &getIterator() : \Generator
    {
        if ($this->classLiteralNode instanceof \RectorPrefix20220531\Nette\Neon\Node\LiteralNode) {
            (yield $this->classLiteralNode);
        }
        if ($this->factoryLiteralNode instanceof \RectorPrefix20220531\Nette\Neon\Node\LiteralNode) {
            (yield $this->factoryLiteralNode);
        }
        foreach ($this->setupMethodCalls as $setupMethodCall) {
            (yield $setupMethodCall);
        }
    }
}
