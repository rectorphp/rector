<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/lsp_errors
 * @see \Rector\Tests\Php80\Rector\ClassMethod\AddParamBasedOnParentClassMethodRector\AddParamBasedOnParentClassMethodRectorTest
 */
final class AddParamBasedOnParentClassMethodRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\VendorLocker\ParentClassMethodTypeOverrideGuard
     */
    private $parentClassMethodTypeOverrideGuard;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    public function __construct(ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, AstResolver $astResolver, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->astResolver = $astResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::FATAL_ERROR_ON_INCOMPATIBLE_METHOD_SIGNATURE;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add missing parameter based on parent class method', [new CodeSample(<<<'CODE_SAMPLE'
class A
{
    public function execute($foo)
    {
    }
}

class B extends A{
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

class B extends A{
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
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->nodeNameResolver->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }
        $parentMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($node);
        if (!$parentMethodReflection instanceof MethodReflection) {
            return null;
        }
        $parentClassMethod = $this->astResolver->resolveClassMethodFromMethodReflection($parentMethodReflection);
        if (!$parentClassMethod instanceof ClassMethod) {
            return null;
        }
        if ($parentClassMethod->isPrivate()) {
            return null;
        }
        $currentClassMethodParams = $node->getParams();
        $parentClassMethodParams = $parentClassMethod->getParams();
        $countCurrentClassMethodParams = \count($currentClassMethodParams);
        $countParentClassMethodParams = \count($parentClassMethodParams);
        if ($countCurrentClassMethodParams === $countParentClassMethodParams) {
            return null;
        }
        if ($countCurrentClassMethodParams < $countParentClassMethodParams) {
            return $this->processReplaceClassMethodParams($node, $parentClassMethod, $currentClassMethodParams, $parentClassMethodParams);
        }
        return $this->processAddNullDefaultParam($node, $currentClassMethodParams, $parentClassMethodParams);
    }
    /**
     * @param Param[] $currentClassMethodParams
     * @param Param[] $parentClassMethodParams
     */
    private function processAddNullDefaultParam(ClassMethod $classMethod, array $currentClassMethodParams, array $parentClassMethodParams) : ?ClassMethod
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
            $currentClassMethodParams[$key]->default = $this->nodeFactory->createNull();
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $classMethod;
    }
    /**
     * @param array<int, Param> $currentClassMethodParams
     * @param array<int, Param> $parentClassMethodParams
     */
    private function processReplaceClassMethodParams(ClassMethod $node, ClassMethod $parentClassMethod, array $currentClassMethodParams, array $parentClassMethodParams) : ?ClassMethod
    {
        $originalParams = $node->params;
        foreach ($parentClassMethodParams as $key => $parentClassMethodParam) {
            if (isset($currentClassMethodParams[$key])) {
                $currentParamName = $this->nodeNameResolver->getName($currentClassMethodParams[$key]);
                $collectParamNamesNextKey = $this->collectParamNamesNextKey($parentClassMethod, $key);
                if (\in_array($currentParamName, $collectParamNamesNextKey, \true)) {
                    $node->params = $originalParams;
                    return null;
                }
                continue;
            }
            $isUsedInStmts = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, function (Node $subNode) use($parentClassMethodParam) : bool {
                if (!$subNode instanceof Variable) {
                    return \false;
                }
                return $this->nodeComparator->areNodesEqual($subNode, $parentClassMethodParam->var);
            });
            if ($isUsedInStmts) {
                $node->params = $originalParams;
                return null;
            }
            $paramDefault = $parentClassMethodParam->default;
            if ($paramDefault instanceof Expr) {
                $printParamDefault = $this->betterStandardPrinter->print($paramDefault);
                $paramDefault = new ConstFetch(new Name($printParamDefault));
            }
            $paramName = $this->nodeNameResolver->getName($parentClassMethodParam);
            $paramType = $this->resolveParamType($parentClassMethodParam);
            $node->params[$key] = new Param(new Variable($paramName), $paramDefault, $paramType, $parentClassMethodParam->byRef, $parentClassMethodParam->variadic, [], $parentClassMethodParam->flags);
            if ($parentClassMethodParam->attrGroups !== []) {
                $attrGroupsAsComment = $this->betterStandardPrinter->print($parentClassMethodParam->attrGroups);
                $node->params[$key]->setAttribute(AttributeKey::COMMENTS, [new Comment($attrGroupsAsComment)]);
            }
        }
        return $node;
    }
    /**
     * @return null|\PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\ComplexType
     */
    private function resolveParamType(Param $param)
    {
        if ($param->type === null) {
            return null;
        }
        $paramType = $param->type;
        $paramType->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        return $paramType;
    }
    /**
     * @return string[]
     */
    private function collectParamNamesNextKey(ClassMethod $classMethod, int $key) : array
    {
        $paramNames = [];
        foreach ($classMethod->params as $paramKey => $param) {
            if ($paramKey > $key) {
                $paramNames[] = $this->nodeNameResolver->getName($param);
            }
        }
        return $paramNames;
    }
}
