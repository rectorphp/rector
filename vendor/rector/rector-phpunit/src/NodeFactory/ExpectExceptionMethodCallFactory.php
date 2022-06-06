<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PHPUnit\NodeFactory;

use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\PHPUnit\PhpDoc\PhpDocValueToNodeMapper;
final class ExpectExceptionMethodCallFactory
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    /**
     * @readonly
     * @var \Rector\PHPUnit\PhpDoc\PhpDocValueToNodeMapper
     */
    private $phpDocValueToNodeMapper;
    public function __construct(NodeFactory $nodeFactory, PhpDocValueToNodeMapper $phpDocValueToNodeMapper)
    {
        $this->nodeFactory = $nodeFactory;
        $this->phpDocValueToNodeMapper = $phpDocValueToNodeMapper;
    }
    /**
     * @param PhpDocTagNode[] $phpDocTagNodes
     * @return Expression[]
     */
    public function createFromTagValueNodes(array $phpDocTagNodes, string $methodName) : array
    {
        $methodCallExpressions = [];
        foreach ($phpDocTagNodes as $phpDocTagNode) {
            $methodCall = $this->createMethodCall($phpDocTagNode, $methodName);
            $methodCallExpressions[] = new Expression($methodCall);
        }
        return $methodCallExpressions;
    }
    private function createMethodCall(PhpDocTagNode $phpDocTagNode, string $methodName) : MethodCall
    {
        if (!$phpDocTagNode->value instanceof GenericTagValueNode) {
            throw new ShouldNotHappenException();
        }
        $node = $phpDocTagNode->name === '@expectedExceptionMessage' ? new String_($phpDocTagNode->value->value) : $this->phpDocValueToNodeMapper->mapGenericTagValueNode($phpDocTagNode->value);
        return $this->nodeFactory->createMethodCall('this', $methodName, [new Arg($node)]);
    }
}
