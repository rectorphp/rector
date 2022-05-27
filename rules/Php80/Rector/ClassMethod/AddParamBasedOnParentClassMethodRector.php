<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\ClassMethod;

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
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\PhpParser\AstResolver;
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
final class AddParamBasedOnParentClassMethodRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
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
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(\Rector\VendorLocker\ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, \Rector\Core\PhpParser\AstResolver $astResolver, \Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->astResolver = $astResolver;
        $this->nodePrinter = $nodePrinter;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::FATAL_ERROR_ON_INCOMPATIBLE_METHOD_SIGNATURE;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add missing parameter based on parent class method', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->nodeNameResolver->isName($node, \Rector\Core\ValueObject\MethodName::CONSTRUCT)) {
            return null;
        }
        $parentMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($node);
        if (!$parentMethodReflection instanceof \PHPStan\Reflection\MethodReflection) {
            return null;
        }
        $parentClassMethod = $this->astResolver->resolveClassMethodFromMethodReflection($parentMethodReflection);
        if (!$parentClassMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
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
    private function processAddNullDefaultParam(\PhpParser\Node\Stmt\ClassMethod $classMethod, array $currentClassMethodParams, array $parentClassMethodParams) : ?\PhpParser\Node\Stmt\ClassMethod
    {
        $hasChanged = \false;
        foreach ($currentClassMethodParams as $key => $currentClassMethodParam) {
            if (isset($parentClassMethodParams[$key])) {
                continue;
            }
            if ($currentClassMethodParam->default instanceof \PhpParser\Node\Expr) {
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
    private function processReplaceClassMethodParams(\PhpParser\Node\Stmt\ClassMethod $node, \PhpParser\Node\Stmt\ClassMethod $parentClassMethod, array $currentClassMethodParams, array $parentClassMethodParams) : ?\PhpParser\Node\Stmt\ClassMethod
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
            $isUsedInStmts = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped($node, function (\PhpParser\Node $subNode) use($parentClassMethodParam) : bool {
                if (!$subNode instanceof \PhpParser\Node\Expr\Variable) {
                    return \false;
                }
                return $this->nodeComparator->areNodesEqual($subNode, $parentClassMethodParam->var);
            });
            if ($isUsedInStmts) {
                $node->params = $originalParams;
                return null;
            }
            $paramDefault = $parentClassMethodParam->default;
            if ($paramDefault instanceof \PhpParser\Node\Expr) {
                $printParamDefault = $this->nodePrinter->print($paramDefault);
                $paramDefault = new \PhpParser\Node\Expr\ConstFetch(new \PhpParser\Node\Name($printParamDefault));
            }
            $paramName = $this->nodeNameResolver->getName($parentClassMethodParam);
            $paramType = $this->resolveParamType($parentClassMethodParam);
            $node->params[$key] = new \PhpParser\Node\Param(new \PhpParser\Node\Expr\Variable($paramName), $paramDefault, $paramType, $parentClassMethodParam->byRef, $parentClassMethodParam->variadic, [], $parentClassMethodParam->flags, $parentClassMethodParam->attrGroups);
        }
        return $node;
    }
    /**
     * @return null|\PhpParser\Node\Identifier|\PhpParser\Node\Name|\PhpParser\Node\ComplexType
     */
    private function resolveParamType(\PhpParser\Node\Param $param)
    {
        if ($param->type === null) {
            return null;
        }
        $paramType = $param->type;
        $paramType->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        return $paramType;
    }
    /**
     * @return string[]
     */
    private function collectParamNamesNextKey(\PhpParser\Node\Stmt\ClassMethod $classMethod, int $key) : array
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
