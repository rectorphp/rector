<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Mod;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\LNumber;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_passing_integers_outside_the_interval_0_255_to_chr
 * @see \Rector\Tests\Php85\Rector\FuncCall\ChrArgModuloRector\ChrArgModuloRectorTest
 */
final class ChrArgModuloRector extends AbstractRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Wrap chr() argument with % 256 to avoid deprecated out-of-range integers', [new CodeSample(<<<'CODE_SAMPLE'
echo chr(300);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo chr(300 % 256);
CODE_SAMPLE
)]);
    }
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!$this->isName($node, 'chr')) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($node->args[0])) {
            return null;
        }
        $argExpr = $args[0]->value;
        if ($argExpr instanceof Mod) {
            return null;
        }
        $value = $this->valueResolver->getValue($argExpr);
        if (!is_int($value)) {
            return null;
        }
        if ($value >= 0 && $value <= 255) {
            return null;
        }
        $args[0]->value = new Mod($argExpr, new LNumber(256));
        $node->args = $args;
        return $node;
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_OUTSIDE_INTERVEL_VAL_IN_CHR_FUNCTION;
    }
}
