<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Parameter\FeatureFlags;
use Rector\Enum\ObjectReference;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\Class_\StaticToSelfStaticMethodCallOnFinalClassRector\StaticToSelfStaticMethodCallOnFinalClassRectorTest
 */
final class StaticToSelfStaticMethodCallOnFinalClassRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `static::methodCall()` to `self::methodCall()` on final class', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public function d()
    {
        echo static::run();
    }

    private static function run()
    {
        echo 'test';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function d()
    {
        echo self::run();
    }

    private static function run()
    {
        echo 'test';
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if ($node->isAbstract()) {
            return null;
        }
        if (!$node->isFinal() && FeatureFlags::treatClassesAsFinal() === \false) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node->stmts, function (Node $subNode) use(&$hasChanged, $node) : ?StaticCall {
            if (!$subNode instanceof StaticCall) {
                return null;
            }
            if (!$this->isName($subNode->class, ObjectReference::STATIC)) {
                return null;
            }
            // skip dynamic method
            if (!$subNode->name instanceof Identifier) {
                return null;
            }
            $methodName = (string) $this->getName($subNode->name);
            $targetClassMethod = $node->getMethod($methodName);
            // skip call non-existing method from current class to ensure transformation is safe
            if (!$targetClassMethod instanceof ClassMethod) {
                return null;
            }
            // avoid overlapped change
            if (!$targetClassMethod->isStatic()) {
                return null;
            }
            $hasChanged = \true;
            $subNode->class = new Name('self');
            return $subNode;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
