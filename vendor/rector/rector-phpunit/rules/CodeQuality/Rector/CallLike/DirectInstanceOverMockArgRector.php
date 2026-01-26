<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\CallLike;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\Rector\AbstractRector;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\CallLike\DirectInstanceOverMockArgRector\DirectInstanceOverMockArgRectorTest
 */
final class DirectInstanceOverMockArgRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Use direct object instance over mock for specific objects in arg of PHPUnit tests', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->someMethod($this->createMock(Request::class));
    }

    private function someMethod($someClass)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class SomeTest extends TestCase
{
    public function test()
    {
        $this->someMethod(new Request());
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
        $hasChanged = \false;
        if ($node instanceof ArrayItem) {
            return $this->refactorArrayItem($node);
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        foreach ($node->getArgs() as $arg) {
            $firstArg = $this->matchCreateMockMethodCallArg($arg->value);
            if (!$firstArg instanceof Arg) {
                continue;
            }
            $className = $this->valueResolver->getValue($firstArg->value);
            if (!in_array($className, [SymfonyClass::REQUEST, SymfonyClass::REQUEST_STACK])) {
                continue;
            }
            $arg->value = new New_(new FullyQualified($className));
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function matchCreateMockMethodCallArg(Expr $expr): ?Arg
    {
        if (!$expr instanceof MethodCall) {
            return null;
        }
        $methodCall = $expr;
        if (!$this->isName($methodCall->name, 'createMock')) {
            return null;
        }
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        return $methodCall->getArgs()[0];
    }
    private function refactorArrayItem(ArrayItem $arrayItem): ?ArrayItem
    {
        $mockedCallArg = $this->matchCreateMockMethodCallArg($arrayItem->value);
        if (!$mockedCallArg instanceof Arg) {
            return null;
        }
        $className = $this->valueResolver->getValue($mockedCallArg->value);
        if (!in_array($className, [SymfonyClass::REQUEST, SymfonyClass::REQUEST_STACK])) {
            return null;
        }
        $arrayItem->value = new New_(new FullyQualified($className));
        return $arrayItem;
    }
}
