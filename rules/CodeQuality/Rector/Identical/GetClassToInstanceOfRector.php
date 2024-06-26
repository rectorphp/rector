<?php

declare (strict_types=1);
namespace Rector\CodeQuality\Rector\Identical;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated Deprecated since 1.1.2, as can create invalid checks. See https://github.com/rectorphp/rector/issues/8639
 * Use PHPStan to find those case and upgrade manually with care instead.
 */
final class GetClassToInstanceOfRector extends AbstractRector implements DeprecatedInterface
{
    /**
     * @var bool
     */
    private $hasWarned = \false;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes comparison with get_class to instanceof', [new CodeSample('if (EventsListener::class === get_class($event->job)) { }', 'if ($event->job instanceof EventsListener) { }')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Identical::class, NotIdentical::class];
    }
    /**
     * @param Identical|NotIdentical $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->hasWarned) {
            return null;
        }
        \trigger_error(\sprintf('The "%s" rule was deprecated, as its functionality caused bugs. See https://github.com/rectorphp/rector/issues/8639', self::class));
        $this->hasWarned = \true;
        return null;
    }
}
