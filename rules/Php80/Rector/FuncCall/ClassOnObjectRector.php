<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\FuncCall\ClassOnObjectRector\ClassOnObjectRectorTest
 */
final class ClassOnObjectRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change get_class($object) to faster $object::class', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($object)
    {
        return get_class($object);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($object)
    {
        return $object::class;
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
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'get_class')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!isset($node->getArgs()[0])) {
            return new ClassConstFetch(new Name('self'), 'class');
        }
        $object = $node->getArgs()[0]->value;
        return new ClassConstFetch($object, 'class');
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::CLASS_ON_OBJECT;
    }
}
