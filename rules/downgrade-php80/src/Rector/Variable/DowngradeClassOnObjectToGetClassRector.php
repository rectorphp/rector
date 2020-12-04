<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\Variable;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Param;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DowngradePhp80\Tests\Rector\Variable\DowngradeClassOnObjectToGetClassRector\DowngradeClassOnObjectToGetClassRectorTest
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
        return [Variable::class];
    }

    /**
     * @param Variable $node
     */
    public function refactor(Node $node): ?Node
    {
        return [];
    }
}
