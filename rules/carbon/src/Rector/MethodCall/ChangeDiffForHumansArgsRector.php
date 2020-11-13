<?php

declare(strict_types=1);

namespace Rector\Carbon\Rector\MethodCall;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see https://carbon.nesbot.com/docs/#api-carbon-2
 *
 * @see \Rector\Carbon\Tests\Rector\MethodCall\ChangeDiffForHumansArgsRector\ChangeDiffForHumansArgsRectorTest
 */
final class ChangeDiffForHumansArgsRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change methods arguments of diffForHumans() on Carbon\Carbon', [
            new CodeSample(
                <<<'PHP'
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon): void
    {
        $carbon->diffForHumans->diffForHumans(null, true);

        $carbon->diffForHumans->diffForHumans(null, false);
    }
}
PHP

                ,
                <<<'PHP'
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon): void
    {
        $carbon->diffForHumans->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE);

        $carbon->diffForHumans->diffForHumans(null, \Carbon\CarbonInterface::DIFF_RELATIVE_AUTO);
    }
}
PHP

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }

    /**
     * @param \PhpParser\Node\Expr\MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
