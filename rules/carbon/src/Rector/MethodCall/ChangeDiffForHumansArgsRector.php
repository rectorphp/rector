<?php

declare(strict_types=1);

namespace Rector\Carbon\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://carbon.nesbot.com/docs/#api-carbon-2
 *
 * @see \Rector\Carbon\Tests\Rector\MethodCall\ChangeDiffForHumansArgsRector\ChangeDiffForHumansArgsRectorTest
 */
final class ChangeDiffForHumansArgsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change methods arguments of diffForHumans() on Carbon\Carbon',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon): void
    {
        $carbon->diffForHumans(null, true);

        $carbon->diffForHumans(null, false);
    }
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon): void
    {
        $carbon->diffForHumans(null, \Carbon\CarbonInterface::DIFF_ABSOLUTE);

        $carbon->diffForHumans(null, \Carbon\CarbonInterface::DIFF_RELATIVE_AUTO);
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isOnClassMethodCall($node, 'Carbon\Carbon', 'diffForHumans')) {
            return null;
        }

        if (! isset($node->args[1])) {
            return null;
        }

        $secondArgValue = $node->args[1]->value;
        if ($this->valueResolver->isTrue($secondArgValue)) {
            $node->args[1]->value = $this->nodeFactory->createClassConstFetch(
                'Carbon\CarbonInterface',
                'DIFF_ABSOLUTE'
            );
            return $node;
        }

        if ($this->valueResolver->isFalse($secondArgValue)) {
            $node->args[1]->value = $this->nodeFactory->createClassConstFetch(
                'Carbon\CarbonInterface',
                'DIFF_RELATIVE_AUTO'
            );
            return $node;
        }

        return null;
    }
}
