<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ClassReflection;
use Rector\Configuration\Parameter\FeatureFlags;
use Rector\Enum\ObjectReference;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://3v4l.org/VbcrN
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
        if (!$node->isFinal() && FeatureFlags::treatClassesAsFinal($node) === \false) {
            return null;
        }
        $hasChanged = \false;
        $scope = ScopeFetcher::fetch($node);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        $this->traverseNodesWithCallable($node->stmts, function (Node $subNode) use(&$hasChanged, $classReflection) : ?StaticCall {
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
            if (!$classReflection->hasNativeMethod($methodName)) {
                return null;
            }
            $methodReflection = $classReflection->getNativeMethod($methodName);
            // avoid overlapped change
            if (!$methodReflection->isStatic()) {
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
