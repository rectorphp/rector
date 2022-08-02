<?php

declare (strict_types=1);
namespace Rector\Nette\NeonParser\Node\Service_;

use RectorPrefix202208\Nette\Neon\Node\EntityNode;
use RectorPrefix202208\Nette\Neon\Node\LiteralNode;
use Rector\Nette\NeonParser\Node\AbstractVirtualNode;
final class SetupMethodCall extends AbstractVirtualNode
{
    /**
     * @var string
     */
    public $className;
    /**
     * @var \Nette\Neon\Node\LiteralNode
     */
    public $methodNameLiteralNode;
    /**
     * @var \Nette\Neon\Node\EntityNode
     */
    public $entityNode;
    public function __construct(string $className, LiteralNode $methodNameLiteralNode, EntityNode $entityNode)
    {
        $this->className = $className;
        $this->methodNameLiteralNode = $methodNameLiteralNode;
        $this->entityNode = $entityNode;
    }
    public function getMethodName() : string
    {
        return $this->methodNameLiteralNode->toValue();
    }
}
