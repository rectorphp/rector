<?php

declare (strict_types=1);
namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\NodeFactory;
use Rector\PHPUnit\PhpDoc\PhpDocValueToNodeMapper;
final class ExpectExceptionMethodCallFactory
{
    /**
     * @readonly
     */
    private NodeFactory $nodeFactory;
    /**
     * @readonly
     */
    private PhpDocValueToNodeMapper $phpDocValueToNodeMapper;
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
        $expr = $this->createExpectedExpr($phpDocTagNode, $phpDocTagNode->value);
        return $this->nodeFactory->createMethodCall('this', $methodName, [new Arg($expr)]);
    }
    private function createExpectedExpr(PhpDocTagNode $phpDocTagNode, GenericTagValueNode $genericTagValueNode) : Expr
    {
        if ($phpDocTagNode->name === '@expectedExceptionMessage') {
            return new String_($genericTagValueNode->value);
        }
        return $this->phpDocValueToNodeMapper->mapGenericTagValueNode($genericTagValueNode);
    }
}
