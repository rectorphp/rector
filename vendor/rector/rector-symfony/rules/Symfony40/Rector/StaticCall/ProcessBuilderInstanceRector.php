<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony40\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated since Rector 1.1.2, as it can create invalid code.
 * This needs a manual change
 *
 * @see https://github.com/pact-foundation/pact-php/pull/61/files.
 */
final class ProcessBuilderInstanceRector extends AbstractRector implements DeprecatedInterface
{
    /**
     * @var bool
     */
    private $hasWarned = \false;
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns `ProcessBuilder::instance()` to new ProcessBuilder in Process in Symfony. Part of multi-step Rector.', [new CodeSample('$processBuilder = Symfony\\Component\\Process\\ProcessBuilder::instance($args);', '$processBuilder = new Symfony\\Component\\Process\\ProcessBuilder($args);')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->hasWarned) {
            return null;
        }
        \trigger_error(\sprintf('The "%s" rule was deprecated, as it cannot change fluent code builder in a reliable way.', self::class));
        \sleep(3);
        $this->hasWarned = \true;
        return null;
    }
}
