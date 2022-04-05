<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTypeChanger;
use Rector\Core\NodeManipulator\PropertyManipulator;
use Rector\Core\PhpParser\NodeFinder\PropertyFetchFinder;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\PhpDoc\TagRemover\VarTagRemover;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\TypeDeclaration\TypeInferer\PropertyTypeInferer\ConstructorPropertyTypeInferer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromStrictConstructorRector\TypedPropertyFromStrictConstructorRectorTest
 */
final class TypedPropertyFromStrictConstructorRector extends AbstractRector
{
    public function __construct(
        private readonly ConstructorPropertyTypeInferer $constructorPropertyTypeInferer,
        private readonly VarTagRemover $varTagRemover,
        private readonly PhpDocTypeChanger $phpDocTypeChanger,
        private readonly PropertyFetchFinder $propertyFetchFinder,
        private readonly PropertyManipulator $propertyManipulator
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add typed properties based only on strict constructor types', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeObject
{
    private $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeObject
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
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
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->type !== null) {
            return null;
        }

        $varType = $this->constructorPropertyTypeInferer->inferProperty($node);
        if (! $varType instanceof Type) {
            return null;
        }

        if ($varType instanceof MixedType) {
            return null;
        }

        $classLike = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (! $classLike instanceof Class_) {
            return null;
        }

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $varType);
            return $node;
        }

        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($varType, TypeKind::PROPERTY());
        if (! $propertyTypeNode instanceof Node) {
            return null;
        }

        // public property can be anything
        if ($node->isPublic()) {
            $this->phpDocTypeChanger->changeVarType($phpDocInfo, $varType);
            return $node;
        }

        $node->type = $propertyTypeNode;

        if ($this->isDefaultToBeNull($node, $classLike)) {
            $node->props[0]->default = null;
        }

        $this->varTagRemover->removeVarTagIfUseless($phpDocInfo, $node);

        return $node;
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }

    private function isDefaultToBeNull(Property $property, Class_ $class): bool
    {
        $propertyName = $this->nodeNameResolver->getName($property);
        $propertyFetches = $this->propertyFetchFinder->findLocalPropertyFetchesByName($class, $propertyName);

        foreach ($propertyFetches as $propertyFetch) {
            $classMethod = $this->betterNodeFinder->findParentType($propertyFetch, ClassMethod::class);
            if (! $classMethod instanceof ClassMethod) {
                continue;
            }

            if (! $this->nodeNameResolver->isName($classMethod, MethodName::CONSTRUCT)) {
                continue;
            }

            if (! $this->propertyManipulator->isInlineStmtWithConstructMethod($propertyFetch, $classMethod)) {
                return false;
            }
        }

        return true;
    }
}
