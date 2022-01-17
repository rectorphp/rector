<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\PHPUnit\PhpDoc\PhpDocValueToNodeMapper;
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
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory, \Rector\PHPUnit\PhpDoc\PhpDocValueToNodeMapper $phpDocValueToNodeMapper)
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
        foreach ($phpDocTagNodes as $genericTagValueNode) {
            $methodCall = $this->createMethodCall($genericTagValueNode, $methodName);
            $methodCallExpressions[] = new \PhpParser\Node\Stmt\Expression($methodCall);
        }
        return $methodCallExpressions;
    }
    private function createMethodCall(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode $phpDocTagNode, string $methodName) : \PhpParser\Node\Expr\MethodCall
    {
        if (!$phpDocTagNode->value instanceof \PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $node = $phpDocTagNode->name === '@expectedExceptionMessage' ? new \PhpParser\Node\Scalar\String_($phpDocTagNode->value->value) : $this->phpDocValueToNodeMapper->mapGenericTagValueNode($phpDocTagNode->value);
        return $this->nodeFactory->createMethodCall('this', $methodName, [new \PhpParser\Node\Arg($node)]);
    }
}
