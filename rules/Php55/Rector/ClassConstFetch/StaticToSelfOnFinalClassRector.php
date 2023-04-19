<?php

declare (strict_types=1);
namespace Rector\Php55\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/class_name_scalars
 * @changelog https://3v4l.org/AHr9C#v5.5.0
 * @see \Rector\Tests\Php55\Rector\ClassConstFetch\StaticToSelfOnFinalClassRector\StaticToSelfOnFinalClassRectorTest
 */
final class StaticToSelfOnFinalClassRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change `static::class` to `self::class` on final class', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
   public function callOnMe()
   {
       var_dump(static::class);
   }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
   public function callOnMe()
   {
       var_dump(self::class);
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
    public function refactor(Node $node) : ?ClassConstFetch
    {
        if (!$node->class instanceof Name) {
            return null;
        }
        if (!$node->name instanceof Identifier) {
            return null;
        }
        if ($node->class->toString() !== ObjectReference::STATIC) {
            return null;
        }
        if ($node->name->toString() !== 'class') {
            return null;
        }
        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (!$class instanceof Class_) {
            return null;
        }
        if (!$class->isFinal()) {
            return null;
        }
        return $this->nodeFactory->createSelfFetchConstant('class');
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CLASSNAME_CONSTANT;
    }
}
