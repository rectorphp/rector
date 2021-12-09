<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\MethodReflection;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Core\ValueObject\PhpVersionFeature;
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
    public function __construct(
        private readonly ParentClassMethodTypeOverrideGuard $parentClassMethodTypeOverrideGuard,
        private readonly AstResolver $astResolver
    ) {
    }

    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::FATAL_ERROR_ON_INCOMPATIBLE_METHOD_SIGNATURE;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add missing parameter based on parent class method', [
            new CodeSample(
                <<<'CODE_SAMPLE'
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
,
                <<<'CODE_SAMPLE'
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
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->nodeNameResolver->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }

        $parentMethodReflection = $this->parentClassMethodTypeOverrideGuard->getParentClassMethod($node);

        if (! $parentMethodReflection instanceof MethodReflection) {
            return null;
        }

        $parentClassMethod = $this->astResolver->resolveClassMethodFromMethodReflection($parentMethodReflection);
        if (! $parentClassMethod instanceof ClassMethod) {
            return null;
        }

        if ($parentClassMethod->isPrivate()) {
            return null;
        }

        $currentClassMethodParams = $node->getParams();
        $parentClassMethodParams = $parentClassMethod->getParams();

        if (count($currentClassMethodParams) >= count($parentClassMethodParams)) {
            return null;
        }

        return $this->processReplaceClassMethodParams(
            $node,
            $parentClassMethod,
            $currentClassMethodParams,
            $parentClassMethodParams
        );
    }

    /**
     * @param Param[] $currentClassMethodParams
     * @param Param[] $parentClassMethodParams
     */
    private function processReplaceClassMethodParams(
        ClassMethod $node,
        ClassMethod $parentClassMethod,
        array $currentClassMethodParams,
        array $parentClassMethodParams
    ): ?ClassMethod {
        $originalParams = $node->params;

        foreach ($parentClassMethodParams as $key => $parentClassMethodParam) {
            if (isset($currentClassMethodParams[$key])) {
                $currentParamName = $this->nodeNameResolver->getName($currentClassMethodParams[$key]);
                $collectParamNamesNextKey = $this->collectParamNamesNextKey($parentClassMethod, $key);

                if (in_array($currentParamName, $collectParamNamesNextKey, true)) {
                    $node->params = $originalParams;
                    return null;
                }

                continue;
            }

            $isUsedInStmts = (bool) $this->betterNodeFinder->findFirstInFunctionLikeScoped(
                $node,
                function (Node $subNode) use ($parentClassMethodParam): bool {
                    if (! $subNode instanceof Variable) {
                        return false;
                    }

                    return $this->nodeComparator->areNodesEqual($subNode, $parentClassMethodParam->var);
                }
            );

            if ($isUsedInStmts) {
                $node->params = $originalParams;
                return null;
            }

            $paramName = $this->nodeNameResolver->getName($parentClassMethodParam);
            $node->params[$key] = new Param(
                new Variable($paramName),
                $parentClassMethodParam->default,
                $parentClassMethodParam->type,
                $parentClassMethodParam->byRef,
                $parentClassMethodParam->variadic,
                [],
                $parentClassMethodParam->flags,
                $parentClassMethodParam->attrGroups
            );
        }

        return $node;
    }

    /**
     * @return string[]
     */
    private function collectParamNamesNextKey(ClassMethod $classMethod, int $key): array
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
