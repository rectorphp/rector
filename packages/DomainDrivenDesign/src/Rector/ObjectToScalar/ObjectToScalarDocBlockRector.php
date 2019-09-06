<?php declare(strict_types=1);

namespace Rector\DomainDrivenDesign\Rector\ObjectToScalar;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarDocBlockRector\ObjectToScalarDocBlockRectorTest
 */
final class ObjectToScalarDocBlockRector extends AbstractObjectToScalarRector
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

/** @var ValueObject|null */
$name;
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
/**
 * @var string|null
 */
private $name;

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

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class, NullableType::class, Variable::class];
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

    private function refactorProperty(Property $property): ?Property
    {
        foreach ($this->valueObjectsToSimpleTypes as $valueObject => $simpleType) {
            if (! $this->isObjectType($property, $valueObject)) {
                continue;
            }

            $this->docBlockManipulator->changeTypeIncludingChildren($property, $valueObject, $simpleType);

            return $property;
        }

        return null;
    }

    private function refactorNullableType(NullableType $nullableType): ?NullableType
    {
        foreach ($this->valueObjectsToSimpleTypes as $valueObject => $simpleType) {
            $typeName = $this->getName($nullableType->type);
            if ($typeName === null) {
                continue;
            }

            /** @var string $valueObject */
            if (! is_a($typeName, $valueObject, true)) {
                continue;
            }

            // in method parameter update docs as well
            $parentNode = $nullableType->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Param) {
                $this->processParamNode($nullableType, $parentNode, $simpleType);
            }

            return $nullableType;
        }

        return null;
    }

    private function refactorVariableNode(Variable $variable): ?Variable
    {
        foreach ($this->valueObjectsToSimpleTypes as $valueObject => $simpleType) {
            if (! $this->isObjectType($variable, $valueObject)) {
                continue;
            }

            $exprNode = $this->betterNodeFinder->findFirstAncestorInstanceOf($variable, Expr::class);
            $node = $variable;
            if ($exprNode && $exprNode->getAttribute(AttributeKey::PARENT_NODE)) {
                $node = $exprNode->getAttribute(AttributeKey::PARENT_NODE);
            }

            if ($node === null) {
                return null;
            }

            $this->docBlockManipulator->changeTypeIncludingChildren($node, $valueObject, $simpleType);

            return $variable;
        }

        return null;
    }

    private function processParamNode(NullableType $nullableType, Param $param, string $newType): void
    {
        $classMethodNode = $param->getAttribute(AttributeKey::PARENT_NODE);
        if ($classMethodNode === null) {
            return;
        }

        $oldType = $this->namespaceAnalyzer->resolveTypeToFullyQualified((string) $nullableType->type, $nullableType);

        $this->docBlockManipulator->changeTypeIncludingChildren($classMethodNode, $oldType, $newType);
    }
}
