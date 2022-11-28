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
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedPropertyRector\ReturnTypeFromStrictTypedPropertyRectorTest
 */
final class ReturnTypeFromStrictTypedPropertyRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\PHPStan\Type\TypeFactory
     */
    private $typeFactory;
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\VendorLocker\NodeVendorLocker\ClassMethodReturnTypeOverrideGuard
     */
    private $classMethodReturnTypeOverrideGuard;
    public function __construct(TypeFactory $typeFactory, ReflectionResolver $reflectionResolver, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard)
    {
        $this->typeFactory = $typeFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add return method return type based on strict typed property', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->returnType !== null) {
            return null;
        }
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node)) {
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
        $propertyTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::RETURN);
        if (!$propertyTypeNode instanceof Node) {
            return null;
        }
        $node->returnType = $propertyTypeNode;
        return $node;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    /**
     * @return Type[]
     */
    private function resolveReturnPropertyType(ClassMethod $classMethod) : array
    {
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstancesOfInFunctionLikeScoped($classMethod, Return_::class);
        $propertyTypes = [];
        foreach ($returns as $return) {
            if ($return->expr === null) {
                return [];
            }
            if (!$return->expr instanceof PropertyFetch) {
                return [];
            }
            $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($return->expr);
            if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
                return [];
            }
            // all property must have type declaration
            if ($phpPropertyReflection->getNativeType() instanceof MixedType) {
                return [];
            }
            $propertyTypes[] = $phpPropertyReflection->getNativeType();
        }
        return $propertyTypes;
    }
}
