<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Analyser\Scope;
use Rector\Core\Rector\AbstractScopeAwareRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * ::class introduced in php 5.5
 * while $this::class introduced in php 8.0
 *
 * @changelog https://wiki.php.net/rfc/class_name_scalars
 * @changelog https://wiki.php.net/rfc/class_name_literal_on_object
 *
 * @see \Rector\Tests\Php80\Rector\ClassConstFetch\ClassOnThisVariableObjectRector\ClassOnThisVariableObjectRectorTest
 */
final class ClassOnThisVariableObjectRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $this::class to static::class or self::class depends on class modifier', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return $this::class;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        return static::class;
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
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        $className = $node->isFinal() ? 'self' : 'static';
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node, function (Node $node) use(&$hasChanged, $className) : ?ClassConstFetch {
            if (!$node instanceof ClassConstFetch) {
                return null;
            }
            if ($this->shouldSkip($node)) {
                return null;
            }
            $node->class = new Name($className);
            $hasChanged = \true;
            return $node;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CLASS_ON_OBJECT;
    }
    private function shouldSkip(ClassConstFetch $classConstFetch) : bool
    {
        if (!$classConstFetch->class instanceof Variable) {
            return \true;
        }
        if (!\is_string($classConstFetch->class->name)) {
            return \true;
        }
        if (!$this->isName($classConstFetch->class, 'this')) {
            return \true;
        }
        if (!$classConstFetch->name instanceof Identifier) {
            return \true;
        }
        return !$this->isName($classConstFetch->name, 'class');
    }
}
