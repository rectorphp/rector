<?php

declare(strict_types=1);

namespace Rector\Carbon\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://carbon.nesbot.com/docs/#api-carbon-2
 *
 * @see \Rector\Carbon\Tests\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector\ChangeCarbonSingularMethodCallToPluralRectorTest
 */
final class ChangeCarbonSingularMethodCallToPluralRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const SINGULAR_TO_PLURAL_NAMES = [
        'addSecond' => 'addSeconds',
        'subSecond' => 'subSeconds',
        'addMinute' => 'addMinutes',
        'subMinute' => 'subMinutes',
        'addDay' => 'addDays',
        'subDay' => 'subDays',
        'addHour' => 'addHours',
        'subHour' => 'subHours',
        'addWeek' => 'addWeeks',
        'subWeek' => 'subWeeks',
        'addMonth' => 'addMonths',
        'subMonth' => 'subMonths',
        'addYear' => 'addYears',
        'subYear' => 'subYears',
    ];

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change setter methods with args to their plural names on Carbon\Carbon',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinute($value);
    }
}
CODE_SAMPLE

                    ,
                    <<<'CODE_SAMPLE'
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinutes($value);
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
        if ($node->args === []) {
            return null;
        }

        foreach (self::SINGULAR_TO_PLURAL_NAMES as $singularName => $pluralName) {
            if (! $this->isName($node->name, $singularName)) {
                continue;
            }

            $node->name = new Identifier($pluralName);
            return $node;
        }

        return null;
    }
}
