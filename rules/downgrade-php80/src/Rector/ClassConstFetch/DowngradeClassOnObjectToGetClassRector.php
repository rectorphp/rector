<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Param;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp80\Tests\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\DowngradeClassOnObjectToGetClassRectorTest
 */
final class DowngradeClassOnObjectToGetClassRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change $object::class to get_class($object)', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($object)
    {
        return $object::class;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($object)
    {
        return get_class($object);
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class];
    }

    /**
     * @param ClassConstFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, 'class')) {
            return null;
        }

        if (! $node->class instanceof Variable) {
            return null;
        }

        return new FuncCall(new Name('get_class'), [new Arg($node->class)]);
    }
}
