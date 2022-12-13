<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodeQuality\Rector\ClassConstFetch\ConvertStaticPrivateConstantToSelfRector\ConvertStaticPrivateConstantToSelfRectorTest
 * @see https://3v4l.org/8Y0ba
 * @see https://phpstan.org/r/11d4c850-1a40-4fae-b665-291f96104d11
 */
final class ConvertStaticPrivateConstantToSelfRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces static::* access to private constants with self::* on final classes', [new CodeSample(<<<'CODE_SAMPLE'
final class Foo {
    private const BAR = 'bar';
    public function run()
    {
        $bar = static::BAR;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class Foo {
    private const BAR = 'bar';
    public function run()
    {
        $bar = self::BAR;
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [ClassConstFetch::class];
    }
    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node) : ?ClassConstFetch
    {
        if (!$this->isUsingStatic($node)) {
            return null;
        }
        if (!$this->isPrivateConstant($node)) {
            return null;
        }
        $node->class = new Name('self');
        return $node;
    }
    private function isUsingStatic(ClassConstFetch $classConstFetch) : bool
    {
        if (!$classConstFetch->class instanceof Name) {
            return \false;
        }
        return $classConstFetch->class->toString() === 'static';
    }
    private function isPrivateConstant(ClassConstFetch $classConstFetch) : bool
    {
        $class = $this->betterNodeFinder->findParentType($classConstFetch, Class_::class);
        if (!$class instanceof Class_) {
            return \false;
        }
        if (!$class->isFinal()) {
            return \false;
        }
        $constantName = $classConstFetch->name;
        if (!$constantName instanceof Identifier) {
            return \false;
        }
        foreach ($class->getConstants() as $classConst) {
            if (!$this->nodeNameResolver->isName($classConst, $constantName->toString())) {
                continue;
            }
            return $classConst->isPrivate();
        }
        return \false;
    }
}
