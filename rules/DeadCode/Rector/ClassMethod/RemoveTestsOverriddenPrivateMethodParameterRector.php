<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\DeadCode\NodeCollector\OverriddenParameterResolver;
use Rector\DeadCode\NodeManipulator\PrivateMethodParamRemover;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveTestsOverriddenPrivateMethodParameterRector\RemoveTestsOverriddenPrivateMethodParameterRectorTest
 */
final class RemoveTestsOverriddenPrivateMethodParameterRector extends AbstractRector
{
    /**
     * @readonly
     */
    private OverriddenParameterResolver $overriddenParameterResolver;
    /**
     * @readonly
     */
    private PrivateMethodParamRemover $privateMethodParamRemover;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(OverriddenParameterResolver $overriddenParameterResolver, PrivateMethodParamRemover $privateMethodParamRemover, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->overriddenParameterResolver = $overriddenParameterResolver;
        $this->privateMethodParamRemover = $privateMethodParamRemover;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove parameter of private test class method, that is overridden by direct assign before its first use', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->createUser(new User());
    }

    private function createUser($user)
    {
        $user = $this->createMock(User::class);

        return $user;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->createUser();
    }

    private function createUser()
    {
        $user = $this->createMock(User::class);

        return $user;
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
        // narrow scope to test classes for now, as mock overrides are the most common case there
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if (!$classMethod->isPrivate()) {
                continue;
            }
            // constructor is called via new, that is not covered by caller args cleanup
            if ($this->isName($classMethod, MethodName::CONSTRUCT)) {
                continue;
            }
            $overriddenParameters = $this->overriddenParameterResolver->resolve($classMethod);
            if ($overriddenParameters === []) {
                continue;
            }
            if ($this->privateMethodParamRemover->removeParams($node, $classMethod, $overriddenParameters)) {
                $hasChanged = \true;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
