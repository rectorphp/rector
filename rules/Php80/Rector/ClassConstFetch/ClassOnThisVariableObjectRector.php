<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
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
final class ClassOnThisVariableObjectRector extends AbstractRector implements MinPhpVersionInterface
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
        return [ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        $className = $class->isFinal() ? 'self' : 'static';
        $node->class = new Name($className);
        return $node;
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
