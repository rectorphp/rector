<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\BetterReflection\Reflection\Adapter\ReflectionParameter;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ExtendedParameterReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\AddParamBasedOnParentClassMethodRectorTest
 */
final class AddParamBasedOnParentClassMethodRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     */
    private StaticTypeMapper $staticTypeMapper;
    /**
     * @readonly
     */
    private BetterStandardPrinter $betterStandardPrinter;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, StaticTypeMapper $staticTypeMapper, BetterStandardPrinter $betterStandardPrinter, BetterNodeFinder $betterNodeFinder)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->staticTypeMapper = $staticTypeMapper;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::FATAL_ERROR_ON_INCOMPATIBLE_METHOD_SIGNATURE;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add missing parameter based on parent class method', [new CodeSample(<<<'CODE_SAMPLE'
class A
{
    public function execute($foo)
    {
    }
}

class B extends A
{
    public function execute()
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class A
{
    public function execute($foo)
    {
    }
}

class B extends A
{
    public function execute($foo)
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->extends === null && $node->implements === []) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($this->isName($classMethod, MethodName::CONSTRUCT)) {
                continue;
            }
            $parentMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($classMethod);
            if (!$parentMethodReflection instanceof MethodReflection) {
                continue;
            }
            if ($parentMethodReflection->isPrivate()) {
                continue;
            }
            $scope = ScopeFetcher::fetch($node);
            $currentClassReflection = $scope->getClassReflection();
            $isPDO = $currentClassReflection instanceof ClassReflection && $currentClassReflection->is('PDO');
            // It relies on phpstorm stubs that define 2 kind of query method for both php 7.4 and php 8.0
            // @see https://github.com/JetBrains/phpstorm-stubs/blob/e2e898a29929d2f520fe95bdb2109d8fa895ba4a/PDO/PDO.php#L1096-L1126
            if ($isPDO && $parentMethodReflection->getName() === 'query') {
                continue;
            }
            $parentClassReflection = $parentMethodReflection->getDeclaringClass();
            $nativeClassReflection = $parentClassReflection->getNativeReflection();
            if (!$nativeClassReflection->hasMethod($parentMethodReflection->getName())) {
                continue;
            }
            $currentClassMethodParams = $classMethod->getParams();
            $parentClassMethodParams = $nativeClassReflection->getMethod($parentMethodReflection->getName())->getParameters();
            $parentParameterReflections = ParametersAcceptorSelector::combineAcceptors($parentMethodReflection->getVariants())->getParameters();
            $countCurrentClassMethodParams = count($currentClassMethodParams);
            $countParentClassMethodParams = count($parentClassMethodParams);
            if ($countCurrentClassMethodParams === $countParentClassMethodParams) {
                continue;
            }
            if ($countCurrentClassMethodParams < $countParentClassMethodParams) {
                $hasClassMethodChanged = $this->processReplaceClassMethodParams($classMethod, $currentClassMethodParams, $parentClassMethodParams, $parentParameterReflections);
                if ($hasClassMethodChanged) {
                    $hasChanged = \true;
                }
                continue;
            }
            $hasClassMethodChanged = $this->processAddNullDefaultParam($currentClassMethodParams, $parentClassMethodParams);
            if ($hasClassMethodChanged) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param Param[] $currentClassMethodParams
     * @param ReflectionParameter[] $parentClassMethodParams
     */
    private function processAddNullDefaultParam(array $currentClassMethodParams, array $parentClassMethodParams): bool
    {
        $hasChanged = \false;
        foreach ($currentClassMethodParams as $key => $currentClassMethodParam) {
            if (isset($parentClassMethodParams[$key])) {
                continue;
            }
            if ($currentClassMethodParam->default instanceof Expr) {
                continue;
            }
            if ($currentClassMethodParam->variadic) {
                continue;
            }
            $currentClassMethodParam->default = $this->nodeFactory->createNull();
            $hasChanged = \true;
        }
        return $hasChanged;
    }
    /**
     * @param array<int, Param> $currentClassMethodParams
     * @param array<int, ReflectionParameter> $parentClassMethodParams
     * @param array<int, ExtendedParameterReflection> $parentParameterReflections
     */
    private function processReplaceClassMethodParams(ClassMethod $classMethod, array $currentClassMethodParams, array $parentClassMethodParams, array $parentParameterReflections): bool
    {
        $originalParams = $classMethod->params;
        $hasChanged = \false;
        foreach ($parentClassMethodParams as $key => $parentClassMethodParam) {
            if (isset($currentClassMethodParams[$key])) {
                $currentParamName = $this->getName($currentClassMethodParams[$key]);
                $collectParamNamesNextKey = $this->collectParamNamesNextKey($parentClassMethodParams, $key);
                if (in_array($currentParamName, $collectParamNamesNextKey, \true)) {
                    $classMethod->params = $originalParams;
                    return \false;
                }
                continue;
            }
            $isUsedInStmts = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($classMethod, function (Node $subNode) use ($parentClassMethodParam): bool {
                if (!$subNode instanceof Variable) {
                    return \false;
                }
                return $this->isName($subNode, $parentClassMethodParam->getName());
            });
            if ($isUsedInStmts) {
                $classMethod->params = $originalParams;
                return \false;
            }
            $paramDefault = null;
            if ($parentClassMethodParam->isDefaultValueAvailable()) {
                $paramDefault = $this->nodeFactory->createReprintedNode($parentClassMethodParam->getDefaultValueExpression());
            }
            $paramName = $parentClassMethodParam->getName();
            $paramType = $this->resolveParamType($parentParameterReflections[$key] ?? null);
            $classMethod->params[$key] = new Param(new Variable($paramName), $paramDefault, $paramType, $parentClassMethodParam->isPassedByReference(), $parentClassMethodParam->isVariadic());
            $attributeGroups = $this->createAttributeGroups($parentClassMethodParam);
            if ($attributeGroups !== []) {
                $attrGroupsAsComment = $this->betterStandardPrinter->print($attributeGroups);
                $classMethod->params[$key]->setAttribute(AttributeKey::COMMENTS, [new Comment($attrGroupsAsComment)]);
            }
            $hasChanged = \true;
        }
        return $hasChanged;
    }
    /**
     * @return null|\PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\ComplexType
     */
    private function resolveParamType(?ExtendedParameterReflection $extendedParameterReflection)
    {
        if (!$extendedParameterReflection instanceof ExtendedParameterReflection) {
            return null;
        }
        return $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($extendedParameterReflection->getNativeType(), TypeKind::PARAM);
    }
    /**
     * @param ReflectionParameter[] $parentClassMethodParams
     * @return string[]
     */
    private function collectParamNamesNextKey(array $parentClassMethodParams, int $key): array
    {
        $paramNames = [];
        foreach ($parentClassMethodParams as $paramKey => $param) {
            if ($paramKey > $key) {
                $paramNames[] = $param->getName();
            }
        }
        return $paramNames;
    }
    /**
     * @return AttributeGroup[]
     */
    private function createAttributeGroups(ReflectionParameter $reflectionParameter): array
    {
        $attributeGroups = [];
        foreach (method_exists($reflectionParameter, 'getAttributes') ? $reflectionParameter->getAttributes() : [] as $reflectionAttribute) {
            $args = [];
            foreach ($reflectionAttribute->getArgumentsExpressions() as $name => $argumentExpression) {
                $args[] = new Arg($this->nodeFactory->createReprintedNode($argumentExpression), \false, \false, [], is_string($name) ? new Identifier($name) : null);
            }
            $attributeGroups[] = new AttributeGroup([new Attribute(new FullyQualified($reflectionAttribute->getName()), $args)]);
        }
        return $attributeGroups;
    }
}
