<?php declare(strict_types=1);

namespace Rector\DomainDrivenDesign\Rector\ObjectToScalar;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
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
        foreach ($this->valueObjectsToSimpleTypes as $oldType => $newSimpleType) {
            $oldObjectType = new ObjectType($oldType);
            if (! $this->isObjectType($property, $oldObjectType)) {
                continue;
            }

            $newSimpleType = $this->staticTypeMapper->mapStringToPHPStanType($newSimpleType);

            $this->docBlockManipulator->changeType($property, $oldObjectType, $newSimpleType);

            return $property;
        }

        return null;
    }

    private function refactorNullableType(NullableType $nullableType): ?NullableType
    {
        foreach ($this->valueObjectsToSimpleTypes as $valueObject => $newSimpleType) {
            $newSimpleType = $this->staticTypeMapper->mapStringToPHPStanType($newSimpleType);

            $nullableStaticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($nullableType->type);
            $valueObjectType = new ObjectType($valueObject);

            if (! $valueObjectType->equals($nullableStaticType)) {
                continue;
            }

            // in method parameter update docs as well
            $parentNode = $nullableType->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Param) {
                $this->processParamNode($nullableType, $parentNode, $newSimpleType);
            }

            return $nullableType;
        }

        return null;
    }

    private function refactorVariableNode(Variable $variable): ?Variable
    {
        foreach ($this->valueObjectsToSimpleTypes as $oldObject => $simpleType) {
            if (! $this->isObjectType($variable, $oldObject)) {
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

            $oldObjectType = new ObjectType($oldObject);
            $newSimpleType = $this->staticTypeMapper->mapStringToPHPStanType($simpleType);

            $this->docBlockManipulator->changeType($node, $oldObjectType, $newSimpleType);

            return $variable;
        }

        return null;
    }

    private function processParamNode(NullableType $nullableType, Param $param, Type $newType): void
    {
        $classMethodNode = $param->getAttribute(AttributeKey::PARENT_NODE);
        if ($classMethodNode === null) {
            return;
        }

        $paramStaticType = $this->getStaticType($param);
        if (! $paramStaticType instanceof ObjectType) {
            return;
        }

        $this->docBlockManipulator->changeType($classMethodNode, $paramStaticType, $newType);
    }
}
