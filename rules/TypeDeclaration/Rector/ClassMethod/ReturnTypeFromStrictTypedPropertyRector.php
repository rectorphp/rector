<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\ReturnTypeFromStrictTypedPropertyRectorTest
 */
final class ReturnTypeFromStrictTypedPropertyRector extends AbstractRector
{
    public function __construct(
        private TypeFactory $typeFactory,
        private ReflectionResolver $reflectionResolver
    ) {
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
     * @return array<class-string<Node>>
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

        $propertyTypes = $this->resolveReturnPropertyType($node);
        if ($propertyTypes === []) {
            return null;
        }

        // add type to return type
        $propertyType = $this->typeFactory->createMixedPassedOrUnionType($propertyTypes);
        if ($propertyType instanceof MixedType) {
            return null;
        }

        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType);
        if (! $propertyTypeNode instanceof Node) {
            return null;
        }

        $node->returnType = $propertyTypeNode;

        return $node;
    }

    /**
     * @return Type[]
     */
    private function resolveReturnPropertyType(ClassMethod $classMethod): array
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

            $propertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch(
                $return->expr
            );
            if (! $propertyReflection instanceof PhpPropertyReflection) {
                return [];
            }

            if ($propertyReflection->getNativeType() instanceof MixedType) {
                return [];
            }

            $propertyTypes[] = $propertyReflection->getNativeType();
        }

        return $propertyTypes;
    }
}
