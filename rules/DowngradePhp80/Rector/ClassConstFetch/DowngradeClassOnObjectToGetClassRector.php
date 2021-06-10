<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DowngradePhp80\Rector\ClassConstFetch\DowngradeClassOnObjectToGetClassRector\DowngradeClassOnObjectToGetClassRectorTest
 */
final class DowngradeClassOnObjectToGetClassRector extends AbstractRector
{
    /**
     * @var string
     */
    private const GET_CLASS = 'get_class';

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
     * @return array<class-string<Node>>
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
        if ($node->class instanceof Variable) {
            return new FuncCall(new Name(self::GET_CLASS), [new Arg($node->class)]);
        }
        if ($node->class instanceof PropertyFetch) {
            return new FuncCall(new Name(self::GET_CLASS), [new Arg($node->class)]);
        }
        if ($node->class instanceof StaticPropertyFetch) {
            return new FuncCall(new Name(self::GET_CLASS), [new Arg($node->class)]);
        }
        return null;
    }
}
