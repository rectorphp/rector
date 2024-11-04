<?php

declare (strict_types=1);
namespace Rector\Php83\Rector\FuncCall;

use PhpParser\Node;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php83\Rector\FuncCall\RemoveGetClassGetParentClassNoArgsRector\RemoveGetClassGetParentClassNoArgsRectorTest
 */
final class RemoveGetClassGetParentClassNoArgsRector extends AbstractRector implements MinPhpVersionInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        $r = new RuleDefinition('Replace calls to get_class() and get_parent_class() without arguments with self::class and parent::class.', [new CodeSample(<<<'OLD_CODE_SAMPLE'
class Example extends StdClass {
    public function whoAreYou() {
        return get_class() . ' daughter of ' . get_parent_class();
    }
}
OLD_CODE_SAMPLE
, <<<'NEW_CODE_SAMPLE'
class Example extends StdClass {
    public function whoAreYou() {
        return self::class . ' daughter of ' . parent::class;
    }
}
NEW_CODE_SAMPLE
)]);
        return $r;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Node\Expr\FuncCall::class];
    }
    /**
     * @param Node\Expr\FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->getArgs()) !== 0) {
            return null;
        }
        $target = null;
        if ($this->isName($node, 'get_class')) {
            $target = 'self';
        }
        if ($this->isName($node, 'get_parent_class')) {
            $target = 'parent';
        }
        if ($target !== null) {
            return new Node\Expr\ClassConstFetch(new Node\Name([$target]), new Node\VarLikeIdentifier('class'));
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::DEPRECATE_GET_CLASS_WITHOUT_ARGS;
    }
}
