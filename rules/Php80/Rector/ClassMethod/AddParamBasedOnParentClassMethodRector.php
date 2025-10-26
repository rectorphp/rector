<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
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
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private BetterStandardPrinter $betterStandardPrinter;
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, AstResolver $astResolver, BetterStandardPrinter $betterStandardPrinter, BetterNodeFinder $betterNodeFinder)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->astResolver = $astResolver;
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
            $parentClassMethod = $this->astResolver->resolveClassMethodFromMethodReflection($parentMethodReflection);
            if (!$parentClassMethod instanceof ClassMethod) {
                continue;
            }
            $currentClassMethodParams = $classMethod->getParams();
            $parentClassMethodParams = $parentClassMethod->getParams();
            $countCurrentClassMethodParams = count($currentClassMethodParams);
            $countParentClassMethodParams = count($parentClassMethodParams);
            if ($countCurrentClassMethodParams === $countParentClassMethodParams) {
                continue;
            }
            if ($countCurrentClassMethodParams < $countParentClassMethodParams) {
                $hasClassMethodChanged = $this->processReplaceClassMethodParams($classMethod, $parentClassMethod, $currentClassMethodParams, $parentClassMethodParams);
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
     * @param Param[] $parentClassMethodParams
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
     * @param array<int, Param> $parentClassMethodParams
     */
    private function processReplaceClassMethodParams(ClassMethod $node, ClassMethod $parentClassMethod, array $currentClassMethodParams, array $parentClassMethodParams): bool
    {
        $originalParams = $node->params;
        $hasChanged = \false;
        foreach ($parentClassMethodParams as $key => $parentClassMethodParam) {
            if (isset($currentClassMethodParams[$key])) {
                $currentParamName = $this->getName($currentClassMethodParams[$key]);
                $collectParamNamesNextKey = $this->collectParamNamesNextKey($parentClassMethod, $key);
                if (in_array($currentParamName, $collectParamNamesNextKey, \true)) {
                    $node->params = $originalParams;
                    return \false;
                }
                continue;
            }
            $isUsedInStmts = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, function (Node $subNode) use ($parentClassMethodParam): bool {
                if (!$subNode instanceof Variable) {
                    return \false;
                }
                return $this->nodeComparator->areNodesEqual($subNode, $parentClassMethodParam->var);
            });
            if ($isUsedInStmts) {
                $node->params = $originalParams;
                return \false;
            }
            $paramDefault = $parentClassMethodParam->default;
            if ($paramDefault instanceof Expr) {
                $paramDefault = $this->nodeFactory->createReprintedNode($paramDefault);
            }
            $paramName = $this->getName($parentClassMethodParam);
            $paramType = $this->resolveParamType($parentClassMethodParam);
            $node->params[$key] = new Param(new Variable($paramName), $paramDefault, $paramType, $parentClassMethodParam->byRef, $parentClassMethodParam->variadic, [], $parentClassMethodParam->flags);
            if ($parentClassMethodParam->attrGroups !== []) {
                $attrGroupsAsComment = $this->betterStandardPrinter->print($parentClassMethodParam->attrGroups);
                $node->params[$key]->setAttribute(AttributeKey::COMMENTS, [new Comment($attrGroupsAsComment)]);
            }
            $hasChanged = \true;
        }
        return $hasChanged;
    }
    /**
     * @return null|\PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\ComplexType
     */
    private function resolveParamType(Param $param)
    {
        if (!$param->type instanceof Node) {
            return null;
        }
        return $this->nodeFactory->createReprintedNode($param->type);
    }
    /**
     * @return string[]
     */
    private function collectParamNamesNextKey(ClassMethod $classMethod, int $key): array
    {
        $paramNames = [];
        foreach ($classMethod->params as $paramKey => $param) {
            if ($paramKey > $key) {
                $paramNames[] = $this->getName($param);
            }
        }
        return $paramNames;
    }
}
