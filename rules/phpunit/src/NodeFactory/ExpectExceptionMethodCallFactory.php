<?php

declare(strict_types=1);

namespace Rector\PHPUnit\NodeFactory;

use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\PHPUnit\PhpDoc\PhpDocValueToNodeMapper;

final class ExpectExceptionMethodCallFactory
{
    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    /**
     * @var PhpDocValueToNodeMapper
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
    public function createFromTagValueNodes(array $phpDocTagNodes, string $methodName): array
    {
        $methodCallExpressions = [];
        foreach ($phpDocTagNodes as $genericTagValueNode) {
            $methodCall = $this->createMethodCall($genericTagValueNode, $methodName);
            $methodCallExpressions[] = new Expression($methodCall);
        }

        return $methodCallExpressions;
    }

    private function createMethodCall(PhpDocTagNode $phpDocTagNode, string $methodName): MethodCall
    {
        if (! $phpDocTagNode->value instanceof GenericTagValueNode) {
            throw new ShouldNotHappenException();
        }

        $node = $this->phpDocValueToNodeMapper->mapGenericTagValueNode($phpDocTagNode->value);
        return $this->nodeFactory->createMethodCall('this', $methodName, [new Arg($node)]);
    }
}
