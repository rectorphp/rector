<?php

declare (strict_types=1);
namespace Rector\DowngradePhp84\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog wiki.php.net/rfc/correctly_name_the_rounding_mode_and_make_it_an_enum
 *
 * @see \Rector\Tests\DowngradePhp84\Rector\FuncCall\DowngradeRoundingModeEnumRector\DowngradeRoundingModeEnumRectorTest
 */
final class DowngradeRoundingModeEnumRector extends AbstractRector
{
    public function getNodeTypes() : array
    {
        return [FuncCall::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace RoundingMode enum to rounding mode constant in round()', [new CodeSample(<<<'CODE_SAMPLE'
round(1.5, 0, RoundingMode::HalfAwayFromZero);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
round(1.5, 0, PHP_ROUND_HALF_UP);
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isName($node, 'round')) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        $args = $node->getArgs();
        if (\count($args) !== 3) {
            return null;
        }
        if (!isset($args[2])) {
            return null;
        }
        $modeArg = $args[2]->value;
        $hasChanged = \false;
        if ($modeArg instanceof ClassConstFetch && $modeArg->class instanceof FullyQualified && $this->isName($modeArg->class, 'RoundingMode')) {
            if (!$modeArg->name instanceof Identifier) {
                return null;
            }
            switch ($modeArg->name->name) {
                case 'HalfAwayFromZero':
                    $constantName = 'PHP_ROUND_HALF_UP';
                    break;
                case 'HalfTowardsZero':
                    $constantName = 'PHP_ROUND_HALF_DOWN';
                    break;
                case 'HalfEven':
                    $constantName = 'PHP_ROUND_HALF_EVEN';
                    break;
                case 'HalfOdd':
                    $constantName = 'PHP_ROUND_HALF_ODD';
                    break;
                default:
                    $constantName = null;
                    break;
            }
            if ($constantName === null) {
                return null;
            }
            $args[2]->value = new ConstFetch(new FullyQualified($constantName));
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
