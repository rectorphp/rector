<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;

final class ValueObjectRemoverDocBlockRector extends AbstractValueObjectRemoverRector
{
    public function isCandidate(Node $node): bool
    {
        if ($node instanceof Property) {
            return $this->processPropertyCandidate($node);
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

    private function processPropertyCandidate(Property $propertyNode): bool
    {
        $propertyNodeTypes = $this->nodeTypeResolver->resolve($propertyNode);

        return (bool) array_intersect($propertyNodeTypes, $this->getValueObjects());
    }

    private function refactorProperty(Property $propertyNode): Property
    {
        $newType = $this->matchNewType($propertyNode);
        if ($newType === null) {
            return $propertyNode;
        }

        $this->docBlockAnalyzer->replaceVarType($propertyNode, $newType);

        return $propertyNode;
    }

    private function refactorNullableType(NullableType $nullableTypeNode): NullableType
    {
        $newType = $this->matchNewType($nullableTypeNode->type);
        if (! $newType) {
            return $nullableTypeNode;
        }

        $parentNode = $nullableTypeNode->getAttribute(Attribute::PARENT_NODE);

        // in method parameter update docs as well
        if ($parentNode instanceof Param) {
            /** @var ClassMethod $classMethodNode */
            $classMethodNode = $parentNode->getAttribute(Attribute::PARENT_NODE);

            $this->docBlockAnalyzer->renameNullable($classMethodNode, (string) $nullableTypeNode->type, $newType);
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

        // @todo use right away?
        // SingleName - no slashes or partial uses => return
        if (! Strings::contains($oldType, '\\')) {
            return $node;
        }

        // SomeNamespace\SomeName - possibly used only part in docs blocks
        $oldTypeParts = explode('\\', $oldType);
        $oldTypeParts = array_reverse($oldTypeParts);

        $oldType = '';
        foreach ($oldTypeParts as $oldTypePart) {
            $oldType .= $oldTypePart;

            $this->docBlockAnalyzer->renameNullable($node, $oldType, $newType);
            $oldType .= '\\';
        }

        return $variableNode;
    }
}
