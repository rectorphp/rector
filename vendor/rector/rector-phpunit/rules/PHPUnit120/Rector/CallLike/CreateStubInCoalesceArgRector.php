<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit120\Rector\CallLike;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Related change in PHPUnit 12 https://phpunit.expert/articles/testing-with-and-without-dependencies.html
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit120\Rector\CallLike\CreateStubInCoalesceArgRector\CreateStubInCoalesceArgRectorTest
 */
final class CreateStubInCoalesceArgRector extends AbstractRector
{
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use createStub() over createMock() when used as argument/array item coalesce ?? fallback', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestCase
{
    public function test()
    {
        $mockObject = $this->>get('service');
        $this->someMethod($mockObject ?? $this->createMock(SomeClass::class));
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
        $mockObject = $this->>get('service');
        $this->someMethod($mockObject ?? $this->createStub(SomeClass::class));
    }

    private function someMethod($someClass)
    {
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
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if ($node instanceof ArrayItem) {
            return $this->refactorArrayItem($node);
        }
        $hasChanges = \false;
        if ($node->isFirstClassCallable()) {
            return null;
        }
        foreach ($node->getArgs() as $arg) {
            if (!$arg->value instanceof Coalesce) {
                continue;
            }
            $coalesce = $arg->value;
            if (!$coalesce->right instanceof MethodCall) {
                continue;
            }
            $methodCall = $coalesce->right;
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
    private function refactorArrayItem(ArrayItem $arrayItem): ?ArrayItem
    {
        if (!$arrayItem->value instanceof Coalesce) {
            return null;
        }
        $coalesce = $arrayItem->value;
        if (!$coalesce->right instanceof MethodCall) {
            return null;
        }
        $methodCall = $coalesce->right;
        if (!$this->isName($methodCall->name, 'createMock')) {
            return null;
        }
        $methodCall->name = new Identifier('createStub');
        return $arrayItem;
    }
}
