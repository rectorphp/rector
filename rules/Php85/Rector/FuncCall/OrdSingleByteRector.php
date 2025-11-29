<?php

declare (strict_types=1);
namespace Rector\Php85\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\String_;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://wiki.php.net/rfc/deprecations_php_8_5#deprecate_passing_string_which_are_not_one_byte_long_to_ord
 * @see \Rector\Tests\Php85\Rector\FuncCall\OrdSingleByteRector\OrdSingleByteRectorTest
 */
final class OrdSingleByteRector extends AbstractRector implements MinPhpVersionInterface
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
        return new RuleDefinition('Replace ord($str) with ord($str[0])', [new CodeSample(<<<'CODE_SAMPLE'
echo ord('abc');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
echo ord('a');
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
        if (!$this->isName($node, 'ord')) {
            return null;
        }
        $args = $node->getArgs();
        if (!isset($args[0])) {
            return null;
        }
        $firstArg = $args[0];
        $argExpr = $firstArg->value;
        $type = $this->nodeTypeResolver->getNativeType($argExpr);
        if (!$type->isString()->yes() && !$type->isInteger()->yes()) {
            return null;
        }
        $value = $this->valueResolver->getValue($argExpr);
        $isInt = is_int($value);
        if (!$argExpr instanceof Int_) {
            return $this->refactorStringType($argExpr, $isInt, $args, $node);
        }
        return $this->refactorInt($value, $isInt, $args, $node);
    }
    public function provideMinPhpVersion(): int
    {
        return PhpVersionFeature::DEPRECATE_ORD_WITH_MULTIBYTE_STRING;
    }
    /**
     * @param Arg[] $args
     */
    private function refactorStringType(Expr $argExpr, bool $isInt, array $args, FuncCall $funcCall): ?\PhpParser\Node\Expr\FuncCall
    {
        if ($argExpr instanceof ArrayDimFetch) {
            return null;
        }
        if ($isInt) {
            return null;
        }
        $args[0]->value = new ArrayDimFetch($argExpr, new Int_(0));
        $funcCall->args = $args;
        return $funcCall;
    }
    /**
     * @param Arg[] $args
     * @param mixed $value
     */
    private function refactorInt($value, bool $isInt, array $args, FuncCall $funcCall): FuncCall
    {
        $value = (string) $value;
        $byte = $value[0] ?? '';
        $byteValue = $isInt ? new Int_((int) $byte) : new String_($byte);
        $args[0]->value = $byteValue;
        $funcCall->args = $args;
        return $funcCall;
    }
}
