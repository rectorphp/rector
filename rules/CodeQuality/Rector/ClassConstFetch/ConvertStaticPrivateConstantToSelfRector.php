<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassConstFetch\ConvertStaticPrivateConstantToSelfRector\ConvertStaticPrivateConstantToSelfRectorTest
 *
 * @see https://3v4l.org/8Y0ba
 * @see https://phpstan.org/r/11d4c850-1a40-4fae-b665-291f96104d11
 */
final class ConvertStaticPrivateConstantToSelfRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces static::* access to private constants with self::*', [new CodeSample(<<<'CODE_SAMPLE'
final class Foo
{
    private const BAR = 'bar';

    public function run()
    {
        $bar = static::BAR;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class Foo
{
    private const BAR = 'bar';

    public function run()
    {
        $bar = self::BAR;
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
        if ($node->getConstants() === []) {
            return null;
        }
        $class = $node;
        $hasChanged = \false;
        $this->traverseNodesWithCallable($class, function (Node $node) use($class, &$hasChanged) : ?Node {
            if (!$node instanceof ClassConstFetch) {
                return null;
            }
            if (!$this->isUsingStatic($node)) {
                return null;
            }
            if (!$class->isFinal() && !$this->isPrivateConstant($node, $class)) {
                return null;
            }
            $hasChanged = \true;
            $node->class = new Name('self');
            return $node;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isUsingStatic(ClassConstFetch $classConstFetch) : bool
    {
        return $this->isName($classConstFetch->class, 'static');
    }
    private function isPrivateConstant(ClassConstFetch $classConstFetch, Class_ $class) : bool
    {
        $constantName = $this->getConstantName($classConstFetch);
        if ($constantName === null) {
            return \false;
        }
        foreach ($class->getConstants() as $constant) {
            if (!$this->isName($constant, $constantName)) {
                continue;
            }
            return $constant->isPrivate();
        }
        return \false;
    }
    private function getConstantName(ClassConstFetch $classConstFetch) : ?string
    {
        $constantNameIdentifier = $classConstFetch->name;
        if (!$constantNameIdentifier instanceof Identifier) {
            return null;
        }
        return $constantNameIdentifier->toString();
    }
}
