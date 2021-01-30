<?php

declare(strict_types=1);

namespace Rector\PHPUnit\NodeFactory;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPUnit\PHPUnitExpectedExceptionTagValueNode;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\PHPUnit\PhpDoc\PhpDocValueToNodeMapper;
use Rector\StaticTypeMapper\StaticTypeMapper;

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

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    public function __construct(
        NodeFactory $nodeFactory,
        PhpDocValueToNodeMapper $phpDocValueToNodeMapper,
        StaticTypeMapper $staticTypeMapper,
        CurrentNodeProvider $currentNodeProvider
    ) {
        $this->nodeFactory = $nodeFactory;
        $this->phpDocValueToNodeMapper = $phpDocValueToNodeMapper;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->currentNodeProvider = $currentNodeProvider;
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
        if ($phpDocTagNode->value instanceof PHPUnitExpectedExceptionTagValueNode) {
            $value = $this->resolveExpectedValue($phpDocTagNode->value);
            $methodCall = $this->nodeFactory->createMethodCall('this', $methodName);
            $methodCall->args[] = new Arg($value);

            return $methodCall;
        }

        if (! $phpDocTagNode->value instanceof GenericTagValueNode) {
            throw new ShouldNotHappenException();
        }

        $node = $this->phpDocValueToNodeMapper->mapGenericTagValueNode($phpDocTagNode->value);
        return $this->nodeFactory->createMethodCall('this', $methodName, [new Arg($node)]);
    }

    private function resolveExpectedValue(
        PHPUnitExpectedExceptionTagValueNode $phpUnitExpectedExceptionTagValueNode
    ): Expr {
        $expectedTypeNode = $phpUnitExpectedExceptionTagValueNode->getTypeNode();

        $currentNode = $this->currentNodeProvider->getNode();
        if (! $currentNode instanceof Node) {
            throw new ShouldNotHappenException();
        }

        $expectedType = $this->staticTypeMapper->mapPHPStanPhpDocTypeNodeToPHPStanType($expectedTypeNode, $currentNode);
        $expectedNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($expectedType);

        if ($expectedNode instanceof Name) {
            return $this->nodeFactory->createClassConstFetchFromName($expectedNode, 'class');
        }

        if ($expectedTypeNode instanceof IdentifierTypeNode) {
            $className = ltrim($expectedTypeNode->name, '\\');
            return BuilderHelpers::normalizeValue($className);
        }

        throw new ShouldNotHappenException();
    }
}
