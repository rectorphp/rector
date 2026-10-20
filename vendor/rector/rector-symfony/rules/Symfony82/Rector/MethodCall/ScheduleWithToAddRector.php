<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony82\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @changelog https://github.com/symfony/symfony/blob/8.2/UPGRADE-8.2.md#scheduler
 * @see \Rector\Symfony\Tests\Symfony82\Rector\MethodCall\ScheduleWithToAddRector\ScheduleWithToAddRectorTest
 */
final class ScheduleWithToAddRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @var string
     */
    private const SCHEDULE_CLASS = 'Symfony\Component\Scheduler\Schedule';
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/scheduler', '>=8.2');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Replace deprecated Schedule::with() with add() on a new Schedule, as with() always started from an empty Schedule', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Scheduler\Schedule;

$schedule = (new Schedule())->with($recurringMessage);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Scheduler\Schedule;

$schedule = (new Schedule())->add($recurringMessage);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?MethodCall
    {
        if (!$this->isName($node->name, 'with')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType(self::SCHEDULE_CLASS))) {
            return null;
        }
        // with() always derives from a fresh, empty Schedule, so add() must run on a new instance
        $newSchedule = new New_(new FullyQualified(self::SCHEDULE_CLASS));
        return new MethodCall($newSchedule, 'add', $node->getArgs());
    }
}
