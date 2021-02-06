<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\UnionType;
use PHPStan\Type\MixedType;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\ReturnTypeFromStrictTypedPropertyRectorTest
 */
final class ReturnTypeFromStrictTypedPropertyRector extends AbstractRector
{
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add return method return type based on strict typed property', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private int $age = 100;

    public function getAge()
    {
        return $this->age;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private int $age = 100;

    public function getAge(): int
    {
        return $this->age;
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            return null;
        }

        if ($node->returnType !== null) {
            return null;
        }

        $propertyTypeNodes = $this->resolveReturnPropertyTypeNodes($node);
        if ($propertyTypeNodes === []) {
            return null;
        }

        $propertyTypes = [];
        foreach ($propertyTypeNodes as $propertyTypeNode) {
            $propertyTypes[] = $this->staticTypeMapper->mapPhpParserNodePHPStanType($propertyTypeNode);
        }

        // add type to return type
        $propertyType = $this->typeFactory->createMixedPassedOrUnionType($propertyTypes);
        if ($propertyType instanceof MixedType) {
            return null;
        }

        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType);
        if ($propertyTypeNode === null) {
            return null;
        }

        $node->returnType = $propertyTypeNode;

        return $node;
    }

    /**
     * @return array<Identifier|Name|NullableType|UnionType>
     */
    private function resolveReturnPropertyTypeNodes(ClassMethod $classMethod): array
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf($classMethod, Return_::class);

        $propertyTypes = [];
        foreach ($returns as $return) {
            if ($return->expr === null) {
                return [];
            }

            if (! $return->expr instanceof PropertyFetch) {
                return [];
            }

            $property = $this->nodeRepository->findPropertyByPropertyFetch($return->expr);
            if (! $property instanceof Property) {
                return [];
            }

            if ($property->type === null) {
                return [];
            }

            $propertyTypes[] = $property->type;
        }

        return $propertyTypes;
    }
}
