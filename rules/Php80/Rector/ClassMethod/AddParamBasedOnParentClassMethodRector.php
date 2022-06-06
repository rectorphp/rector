<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Php80\Rector\ClassMethod;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\ComplexType;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Param;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PHPStan\Reflection\MethodReflection;
use RectorPrefix20220606\Rector\Core\Contract\PhpParser\NodePrinterInterface;
use RectorPrefix20220606\Rector\Core\PhpParser\AstResolver;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\MethodName;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\VendorLocker\ParentClassMethodTypeOverrideGuard;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    public function __construct(ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard, AstResolver $astResolver, NodePrinterInterface $nodePrinter)
    {
        $this->parentClassMethodTypeOverrideGuard = $parentClassMethodTypeOverrideGuard;
        $this->astResolver = $astResolver;
        $this->nodePrinter = $nodePrinter;
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
                $printParamDefault = $this->nodePrinter->print($paramDefault);
                $paramDefault = new ConstFetch(new Name($printParamDefault));
            }
            $paramName = $this->nodeNameResolver->getName($parentClassMethodParam);
            $paramType = $this->resolveParamType($parentClassMethodParam);
            $node->params[$key] = new Param(new Variable($paramName), $paramDefault, $paramType, $parentClassMethodParam->byRef, $parentClassMethodParam->variadic, [], $parentClassMethodParam->flags, $parentClassMethodParam->attrGroups);
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
