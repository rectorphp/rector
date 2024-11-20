<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\Php\PhpPropertyReflection;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\TypeDeclaration\NodeAnalyzer\ReturnAnalyzer;
use Rector\ValueObject\PhpVersionFeature;
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
     */
    private TypeFactory $typeFactory;
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private ReturnAnalyzer $returnAnalyzer;
    public function __construct(TypeFactory $typeFactory, ReflectionResolver $reflectionResolver, ClassMethodReturnTypeOverrideGuard $classMethodReturnTypeOverrideGuard, BetterNodeFinder $betterNodeFinder, StaticTypeMapper $staticTypeMapper, ReturnAnalyzer $returnAnalyzer)
    {
        $this->typeFactory = $typeFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->classMethodReturnTypeOverrideGuard = $classMethodReturnTypeOverrideGuard;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->returnAnalyzer = $returnAnalyzer;
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
        $scope = ScopeFetcher::fetch($node);
        if ($node->returnType instanceof Node) {
            return null;
        }
        if ($this->classMethodReturnTypeOverrideGuard->shouldSkipClassMethod($node, $scope)) {
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
        $returns = $this->betterNodeFinder->findReturnsScoped($classMethod);
        $propertyTypes = [];
        foreach ($returns as $return) {
            if (!$return->expr instanceof Expr) {
                return [];
            }
            if (!$return->expr instanceof PropertyFetch && !$return->expr instanceof StaticPropertyFetch) {
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
            $propertyTypes[] = $this->nodeTypeResolver->getNativeType($return->expr);
        }
        if (!$this->returnAnalyzer->hasOnlyReturnWithExpr($classMethod, $returns)) {
            return [];
        }
        return $propertyTypes;
    }
}
