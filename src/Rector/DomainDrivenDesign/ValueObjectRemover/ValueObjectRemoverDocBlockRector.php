<?php declare(strict_types=1);

namespace Rector\Rector\DomainDrivenDesign\ValueObjectRemover;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ValueObjectRemoverDocBlockRector extends AbstractValueObjectRemoverRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined value object to simple types in doc blocks', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
/**
 * @var ValueObject|null
 */
private $name;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
/**
 * @var string|null
 */
private $name;
CODE_SAMPLE
                ,
                [
                    '$valueObjectsToSimpleTypes' => [
                        'ValueObject' => 'string',
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
/** @var ValueObject|null */
$name;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
/** @var string|null */
$name;
CODE_SAMPLE
                ,
                [
                    '$valueObjectsToSimpleTypes' => [
                        'ValueObject' => 'string',
                    ],
                ]
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if ($node instanceof Property) {
            return $this->isPropertyCandidate($node);
        }

        // + Variable for docs update
        return $node instanceof NullableType || $node instanceof Variable;
    }

    /**
     * @param Property|NullableType|Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Property) {
            $this->refactorProperty($node);
        }

        if ($node instanceof NullableType) {
            $this->refactorNullableType($node);
        }

        if ($node instanceof Variable) {
            $this->refactorVariableNode($node);
        }

        return $node;
    }

    private function isPropertyCandidate(Property $propertyNode): bool
    {
        $propertyNodeTypes = $this->nodeTypeResolver->resolve($propertyNode);

        return (bool) array_intersect($propertyNodeTypes, $this->getValueObjects());
    }

    private function refactorProperty(Property $propertyNode): void
    {
        $match = $this->matchOriginAndNewType($propertyNode);
        if ($match === null) {
            return;
        }

        [$oldType, $newType] = $match;

        $this->docBlockAnalyzer->changeType($propertyNode, $oldType, $newType);
    }

    private function refactorNullableType(NullableType $nullableTypeNode): void
    {
        $newType = $this->matchNewType($nullableTypeNode->type);
        if ($newType === null) {
            return;
        }

        // in method parameter update docs as well
        $parentNode = $nullableTypeNode->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof Param) {
            $this->processParamNode($nullableTypeNode, $parentNode, $newType);
        }
    }

    private function refactorVariableNode(Variable $variableNode): void
    {
        $match = $this->matchOriginAndNewType($variableNode);
        if (! $match) {
            return;
        }

        [$oldType, $newType] = $match;

        $exprNode = $this->betterNodeFinder->findFirstAncestorInstanceOf($variableNode, Expr::class);
        $node = $variableNode;
        if ($exprNode && $exprNode->getAttribute(Attribute::PARENT_NODE)) {
            $node = $exprNode->getAttribute(Attribute::PARENT_NODE);
        }

        $this->docBlockAnalyzer->changeType($node, $oldType, $newType);
    }

    private function processParamNode(NullableType $nullableTypeNode, Param $paramNode, string $newType): void
    {
        /** @var ClassMethod $classMethodNode */
        $classMethodNode = $paramNode->getAttribute(Attribute::PARENT_NODE);

        $oldType = $this->namespaceAnalyzer->resolveTypeToFullyQualified(
            (string) $nullableTypeNode->type,
            $nullableTypeNode
        );

        $this->docBlockAnalyzer->changeType($classMethodNode, $oldType, $newType);
    }
}
