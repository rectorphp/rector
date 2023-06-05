<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\Switch_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Switch_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/iGDVW
 *
 * @changelog https://wiki.php.net/rfc/switch.default.multiple https://stackoverflow.com/a/44000794/1348344 https://github.com/franzliedke/wp-mpdf/commit/9dc489215fbd1adcb514810653a73dea71db8e99#diff-2f1f4a51a2dd3a73ca034a48a67a2320L1373
 *
 * @see \Rector\Tests\Php70\Rector\Switch_\ReduceMultipleDefaultSwitchRector\ReduceMultipleDefaultSwitchRectorTest
 */
final class ReduceMultipleDefaultSwitchRector extends AbstractRector implements MinPhpVersionInterface
{
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::NO_MULTIPLE_DEFAULT_SWITCH;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove first default switch, that is ignored', [new CodeSample(<<<'CODE_SAMPLE'
switch ($expr) {
    default:
         echo "Hello World";

    default:
         echo "Goodbye Moon!";
         break;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
switch ($expr) {
    default:
         echo "Goodbye Moon!";
         break;
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
        $defaultCases = [];
        foreach ($node->cases as $key => $case) {
            if ($case->cond instanceof Expr) {
                continue;
            }
            $defaultCases[$key] = $case;
        }
        $defaultCaseCount = \count($defaultCases);
        if ($defaultCaseCount < 2) {
            return null;
        }
        foreach ($node->cases as $key => $case) {
            if ($case->cond instanceof Expr) {
                continue;
            }
            // remove previous default cases
            if ($defaultCaseCount > 1) {
                unset($node->cases[$key]);
                --$defaultCaseCount;
            }
        }
        return $node;
    }
}
