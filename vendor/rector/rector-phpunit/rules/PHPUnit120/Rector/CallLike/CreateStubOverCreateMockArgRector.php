<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\CallLike;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Related change in PHPUnit 12 https://phpunit.expert/articles/testing-with-and-without-dependencies.html
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\CallLike\CreateStubOverCreateMockArgRector\CreateStubOverCreateMockArgRectorTest
 */
final class CreateStubOverCreateMockArgRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use createStub() over createMock() when used as argument or array value and does not add any mock requirements', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
final class SomeTest extends TestCase
{
    public function test()
    {
        $this->someMethod($this->createMock(SomeClass::class));
    }

    private function someMethod($someClass)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->someMethod($this->createStub(SomeClass::class));
    }

    private function someMethod($someClass)
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
        return [StaticCall::class, MethodCall::class, New_::class, ArrayItem::class];
    }
    /**
     * @param MethodCall|StaticCall|New_|ArrayItem $node
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\New_|\PhpParser\Node\ArrayItem|null
     */
    public function refactor(Node $node)
    {
        $scope = ScopeFetcher::fetch($node);
        if (!$scope->isInClass()) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection->is(PHPUnitClassName::TEST_CASE)) {
            return null;
        }
        if ($node instanceof ArrayItem) {
            if (!$node->value instanceof MethodCall) {
                return null;
            }
            $methodCall = $node->value;
            if (!$this->isName($methodCall->name, 'createMock')) {
                return null;
            }
            $methodCall->name = new Identifier('createStub');
            return $node;
        }
        $hasChanges = \false;
        if ($node->isFirstClassCallable()) {
            return null;
        }
        foreach ($node->getArgs() as $arg) {
            if (!$arg->value instanceof MethodCall) {
                continue;
            }
            $methodCall = $arg->value;
            if (!$this->isName($methodCall->name, 'createMock')) {
                continue;
            }
            $methodCall->name = new Identifier('createStub');
            $hasChanges = \true;
        }
        if ($hasChanges) {
            return $node;
        }
        return null;
    }
}
