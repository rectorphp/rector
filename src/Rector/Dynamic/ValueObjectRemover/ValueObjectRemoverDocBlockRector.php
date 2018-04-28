<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic\ValueObjectRemover;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ValueObjectRemoverDocBlockRector extends AbstractValueObjectRemoverRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns defined value object to simple types in doc blocks', [
            new CodeSample(
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
            ),
            new CodeSample(
                <<<'CODE_SAMPLE'
/** @var ValueObject|null */
$name;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
/** @var string|null */
$name;
CODE_SAMPLE
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
            return $this->refactorProperty($node);
        }

        if ($node instanceof NullableType) {
            return $this->refactorNullableType($node);
        }

        if ($node instanceof Variable) {
            return $this->refactorVariableNode($node);
        }

        return null;
    }

    private function isPropertyCandidate(Property $propertyNode): bool
    {
        $propertyNodeTypes = $this->nodeTypeResolver->resolve($propertyNode);

        return (bool) array_intersect($propertyNodeTypes, $this->getValueObjects());
    }

    private function refactorProperty(Property $propertyNode): Property
    {
        $match = $this->matchOriginAndNewType($propertyNode);
        if ($match === null) {
            return $propertyNode;
        }

        [$oldType, $newType] = $match;

        $this->docBlockAnalyzer->renameNullable($propertyNode, $oldType, $newType);

        return $propertyNode;
    }

    private function refactorNullableType(NullableType $nullableTypeNode): NullableType
    {
        $newType = $this->matchNewType($nullableTypeNode->type);

        if ($newType === null) {
            return $nullableTypeNode;
        }

        $parentNode = $nullableTypeNode->getAttribute(Attribute::PARENT_NODE);

        // in method parameter update docs as well
        if ($parentNode instanceof Param) {
            /** @var ClassMethod $classMethodNode */
            $classMethodNode = $parentNode->getAttribute(Attribute::PARENT_NODE);

            $oldType = $this->namespaceAnalyzer->resolveTypeToFullyQualified(
                [(string) $nullableTypeNode->type],
                $nullableTypeNode
            );
            $this->docBlockAnalyzer->renameNullable($classMethodNode, $oldType, $newType);
        }

        return $nullableTypeNode;
    }

    private function refactorVariableNode(Variable $variableNode): Variable
    {
        $match = $this->matchOriginAndNewType($variableNode);
        if (! $match) {
            return $variableNode;
        }

        [$oldType, $newType] = $match;

        $exprNode = $this->betterNodeFinder->findFirstAncestorInstanceOf($variableNode, Expr::class);
        $node = $variableNode;
        if ($exprNode && $exprNode->getAttribute(Attribute::PARENT_NODE)) {
            $node = $exprNode->getAttribute(Attribute::PARENT_NODE);
        }

        $this->docBlockAnalyzer->renameNullable($node, $oldType, $newType);

        return $variableNode;
    }
}
