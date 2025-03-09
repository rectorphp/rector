<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony43\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ClassReflection;
use Rector\Enum\ObjectReference;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony43\Rector\ClassMethod\EventDispatcherParentConstructRector\EventDispatcherParentConstructRectorTest
 */
final class EventDispatcherParentConstructRector extends AbstractRector
{
    /**
     * @readonly
     */
    private BetterNodeFinder $betterNodeFinder;
    public function __construct(BetterNodeFinder $betterNodeFinder)
    {
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removes parent construct method call in EventDispatcher class', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventDispatcher;

final class SomeEventDispatcher extends EventDispatcher
{
    public function __construct()
    {
        $value = 1000;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\EventDispatcher\EventDispatcher;

final class SomeEventDispatcher extends EventDispatcher
{
    public function __construct()
    {
        $value = 1000;
        parent::__construct();
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
        $scope = ScopeFetcher::fetch($node);
        if (!$scope->isInClass()) {
            return null;
        }
        if (!$this->isName($node->name, MethodName::CONSTRUCT)) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection->is('Symfony\\Contracts\\EventDispatcher\\EventDispatcherInterface')) {
            return null;
        }
        if (!$classReflection->getParentClass() instanceof ClassReflection) {
            return null;
        }
        if ($this->hasParentCallOfMethod($node, MethodName::CONSTRUCT)) {
            return null;
        }
        $node->stmts[] = $this->createParentStaticCall(MethodName::CONSTRUCT);
        return $node;
    }
    private function createParentStaticCall(string $method) : Expression
    {
        $staticCall = $this->nodeFactory->createStaticCall(ObjectReference::PARENT, $method);
        return new Expression($staticCall);
    }
    /**
     * Looks for "parent::<methodName>
     */
    private function hasParentCallOfMethod(ClassMethod $classMethod, string $method) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use($method) : bool {
            if (!$node instanceof StaticCall) {
                return \false;
            }
            if (!$this->isName($node->class, ObjectReference::PARENT)) {
                return \false;
            }
            return $this->isName($node->name, $method);
        });
    }
}
