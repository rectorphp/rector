<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated This rule is deprecated as prevents refactoring to match (), use PHPStan, manual handling and PHP 8.0 upgrade set instead
 */
final class BinarySwitchToIfElseRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes switch with 2 options to if-else', [new CodeSample(<<<'CODE_SAMPLE'
switch ($foo) {
    case 'my string':
        $result = 'ok';
    break;

    default:
        $result = 'not ok';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
if ($foo == 'my string') {
    $result = 'ok';
} else {
    $result = 'not ok';
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Switch_::class];
    }
    /**
     * @param Switch_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        \trigger_error(\sprintf('The "%s" rule is deprecated as prevents refactoring to match (), use PHPStan, manual handling and PHP 8.0 upgrade set instead.', self::class), \E_USER_ERROR);
        return null;
    }
}
