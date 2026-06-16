<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\MethodReflection;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\Renaming\NodeAnalyzer\DeprecatedMethodCallReplacementResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Renaming\Rector\MethodCall\RenameDeprecatedMethodCallRector\RenameDeprecatedMethodCallRectorTest
 */
final class RenameDeprecatedMethodCallRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    /**
     * @readonly
     */
    private DeprecatedMethodCallReplacementResolver $deprecatedMethodCallReplacementResolver;
    public function __construct(ReflectionResolver $reflectionResolver, DeprecatedMethodCallReplacementResolver $deprecatedMethodCallReplacementResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
        $this->deprecatedMethodCallReplacementResolver = $deprecatedMethodCallReplacementResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Rename method calls whose target method is "@deprecated" and suggests a replacement method on the same class', [new CodeSample(<<<'CODE_SAMPLE'
$someObject->oldMethod();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject->newMethod();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($this->getName($node->name) === null) {
            return null;
        }
        $methodReflection = $this->reflectionResolver->resolveFunctionLikeReflectionFromCall($node);
        if (!$methodReflection instanceof MethodReflection) {
            return null;
        }
        $newMethodName = $this->deprecatedMethodCallReplacementResolver->resolve($methodReflection);
        if ($newMethodName === null) {
            return null;
        }
        $node->name = new Identifier($newMethodName);
        return $node;
    }
}
