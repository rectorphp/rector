<?php

declare (strict_types=1);
namespace Rector\Carbon\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://carbon.nesbot.com/docs/#api-carbon-2
 *
 * @see \Rector\Tests\Carbon\Rector\MethodCall\ChangeCarbonSingularMethodCallToPluralRector\ChangeCarbonSingularMethodCallToPluralRectorTest
 */
final class ChangeCarbonSingularMethodCallToPluralRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const SINGULAR_TO_PLURAL_NAMES = ['addSecond' => 'addSeconds', 'subSecond' => 'subSeconds', 'addMinute' => 'addMinutes', 'subMinute' => 'subMinutes', 'addDay' => 'addDays', 'subDay' => 'subDays', 'addHour' => 'addHours', 'subHour' => 'subHours', 'addWeek' => 'addWeeks', 'subWeek' => 'subWeeks', 'addMonth' => 'addMonths', 'subMonth' => 'subMonths', 'addYear' => 'addYears', 'subYear' => 'subYears'];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change setter methods with args to their plural names on Carbon\\Carbon', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinute($value);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Carbon\Carbon;

final class SomeClass
{
    public function run(Carbon $carbon, $value): void
    {
        $carbon->addMinutes($value);
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node->args === []) {
            return null;
        }
        foreach (self::SINGULAR_TO_PLURAL_NAMES as $singularName => $pluralName) {
            if (!$this->isName($node->name, $singularName)) {
                continue;
            }
            $node->name = new \PhpParser\Node\Identifier($pluralName);
            return $node;
        }
        return null;
    }
}
