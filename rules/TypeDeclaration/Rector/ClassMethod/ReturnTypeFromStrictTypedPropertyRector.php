<?php

declare (strict_types=1);
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
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\ReturnTypeFromStrictTypedPropertyRectorTest
 */
final class ReturnTypeFromStrictTypedPropertyRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(\Rector\NodeTypeResolver\PHPStan\Type\TypeFactory $typeFactory, \Rector\Core\Reflection\ReflectionResolver $reflectionResolver)
    {
        $this->typeFactory = $typeFactory;
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add return method return type based on strict typed property', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private int $age = 100;

    public function getAge()
    {
        return $this->age;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private int $age = 100;

    public function getAge(): int
    {
        return $this->age;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->returnType !== null) {
            return null;
        }
        $propertyTypes = $this->resolveReturnPropertyType($node);
        if ($propertyTypes === []) {
            return null;
        }
        // add type to return type
        $propertyType = $this->typeFactory->createMixedPassedOrUnionType($propertyTypes);
        if ($propertyType instanceof \PHPStan\Type\MixedType) {
            return null;
        }
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind::RETURN());
        if (!$propertyTypeNode instanceof \PhpParser\Node) {
            return null;
        }
        $node->returnType = $propertyTypeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::TYPED_PROPERTIES;
    }
    /**
     * @return Type[]
     */
    private function resolveReturnPropertyType(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf($classMethod, \PhpParser\Node\Stmt\Return_::class);
        $propertyTypes = [];
        foreach ($returns as $return) {
            if ($return->expr === null) {
                return [];
            }
            if (!$return->expr instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return [];
            }
            $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($return->expr);
            if (!$phpPropertyReflection instanceof \PHPStan\Reflection\Php\PhpPropertyReflection) {
                return [];
            }
            // all property must have type declaration
            if ($phpPropertyReflection->getNativeType() instanceof \PHPStan\Type\MixedType) {
                return [];
            }
            $propertyTypes[] = $phpPropertyReflection->getNativeType();
        }
        return $propertyTypes;
    }
}
