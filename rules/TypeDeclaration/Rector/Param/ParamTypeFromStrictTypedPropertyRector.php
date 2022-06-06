<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\TypeDeclaration\Rector\Param;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\NullableType;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Analyser\Scope;
use RectorPrefix20220606\PHPStan\Reflection\Php\PhpPropertyReflection;
use RectorPrefix20220606\PHPStan\Type\MixedType;
use RectorPrefix20220606\PHPStan\Type\Type;
use RectorPrefix20220606\PHPStan\Type\UnionType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\Reflection\ReflectionResolver;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use RectorPrefix20220606\Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\TypeDeclaration\Rector\Param\ParamTypeFromStrictTypedPropertyRector\ParamTypeFromStrictTypedPropertyRectorTest
 */
final class ParamTypeFromStrictTypedPropertyRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    public function __construct(ReflectionResolver $reflectionResolver, ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add param type from $param set to typed property', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    private int $age;

    public function setAge($age)
    {
        $this->age = $age;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    private int $age;

    public function setAge(int $age)
    {
        $this->age = $age;
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
        return [Param::class];
    }
    /**
     * @param Param $node
     */
    public function refactor(Node $node) : ?Node
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parent instanceof ClassMethod) {
            return null;
        }
        return $this->decorateParamWithType($parent, $node);
    }
    public function decorateParamWithType(ClassMethod $classMethod, Param $param) : ?Param
    {
        if ($param->type !== null) {
            return null;
        }
        if ($this->parentClassMethodTypeOverrideGuard->hasParentClassMethod($classMethod)) {
            return null;
        }
        $originalParamType = $this->resolveParamOriginalType($param);
        $paramName = $this->getName($param);
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstanceOf((array) $classMethod->getStmts(), Assign::class);
        foreach ($assigns as $assign) {
            if (!$this->nodeComparator->areNodesEqual($assign->expr, $param->var)) {
                continue;
            }
            if (!$assign->var instanceof PropertyFetch) {
                continue;
            }
            if ($this->hasTypeChangedBeforeAssign($assign, $paramName, $originalParamType)) {
                return null;
            }
            $singlePropertyTypeNode = $this->matchPropertySingleTypeNode($assign->var);
            if (!$singlePropertyTypeNode instanceof Node) {
                return null;
            }
            $param->type = $singlePropertyTypeNode;
            return $param;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
    /**
     * @return Name|ComplexType|null
     */
    private function matchPropertySingleTypeNode(PropertyFetch $propertyFetch) : ?Node
    {
        $phpPropertyReflection = $this->reflectionResolver->resolvePropertyReflectionFromPropertyFetch($propertyFetch);
        if (!$phpPropertyReflection instanceof PhpPropertyReflection) {
            return null;
        }
        $propertyType = $phpPropertyReflection->getNativeType();
        if ($propertyType instanceof MixedType) {
            return null;
        }
        if ($propertyType instanceof UnionType) {
            return null;
        }
        if ($propertyType instanceof NullableType) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType, TypeKind::PROPERTY);
    }
    private function hasTypeChangedBeforeAssign(Assign $assign, string $paramName, Type $originalType) : bool
    {
        $scope = $assign->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return \false;
        }
        if (!$scope->hasVariableType($paramName)->yes()) {
            return \false;
        }
        $currentParamType = $scope->getVariableType($paramName);
        return !$currentParamType->equals($originalType);
    }
    private function resolveParamOriginalType(Param $param) : Type
    {
        $scope = $param->getAttribute(AttributeKey::SCOPE);
        if (!$scope instanceof Scope) {
            return new MixedType();
        }
        $paramName = $this->getName($param);
        if (!$scope->hasVariableType($paramName)->yes()) {
            return new MixedType();
        }
        return $scope->getVariableType($paramName);
    }
}
