<?php

declare (strict_types=1);
namespace Rector\Carbon\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Minus;
use PhpParser\Node\Expr\BinaryOp\Mul;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Carbon\Rector\FuncCall\DateFuncCallToCarbonRector\DateFuncCallToCarbonRectorTest
 */
final class DateFuncCallToCarbonRector extends AbstractRector
{
    private const TIME_UNITS = [['weeks', 604800], ['days', 86400], ['hours', 3600], ['minutes', 60], ['seconds', 1]];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert `date()` function call to `Carbon::now()->format(*)`', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $date = date('Y-m-d');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $date = \Carbon\Carbon::now()->format('Y-m-d');
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
        return [Minus::class, FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Minus) {
            $left = $node->left;
            if ($left instanceof FuncCall && !$left->isFirstClassCallable() && $this->isName($left->name, 'time')) {
                $timeUnit = $this->detectTimeUnit($node->right);
                if ($timeUnit !== null) {
                    return $this->createCarbonSubtract($timeUnit);
                }
            }
            return null;
        }
        if (!$node instanceof FuncCall) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if ($this->isName($node->name, 'date') && isset($node->args[1]) && $node->args[1] instanceof Arg) {
            $format = $this->getArgValue($node, 0);
            if (!$format instanceof Expr) {
                return null;
            }
            $timestamp = $node->args[1]->value;
            if ($timestamp instanceof FuncCall && $this->isName($timestamp->name, 'strtotime') && isset($timestamp->args[0]) && $timestamp->args[0] instanceof Arg) {
                $dateExpr = $timestamp->args[0]->value;
                return $this->createCarbonParseFormat($dateExpr, $format);
            }
            // @phpstan-ignore if.alwaysTrue
            if ($this->getType($timestamp)->isInteger()) {
                return $this->createCarbonFromTimestamp($timestamp, $format);
            }
        }
        if ($this->isName($node->name, 'date') && isset($node->args[0])) {
            $format = $this->getArgValue($node, 0);
            if ($format instanceof String_) {
                return $this->createCarbonNowFormat($format);
            }
        }
        if ($this->isName($node->name, 'strtotime') && isset($node->args[0])) {
            $dateExpr = $this->getArgValue($node, 0);
            $baseTimestamp = $this->getArgValue($node, 1);
            if ($dateExpr instanceof Expr && !$baseTimestamp instanceof Expr) {
                return $this->createCarbonParseTimestamp($dateExpr);
            }
            if ($dateExpr instanceof Expr && $baseTimestamp instanceof String_) {
                $isRelative = \strncmp($baseTimestamp->value, '+', \strlen('+')) === 0 || \strncmp($baseTimestamp->value, '-', \strlen('-')) === 0;
                if ($isRelative) {
                    return null;
                    // @todo implement relative changes based on second arg
                }
            }
        }
        return null;
    }
    private function getArgValue(FuncCall $funcCall, int $index) : ?Expr
    {
        if (!isset($funcCall->args[$index]) || !$funcCall->args[$index] instanceof Arg) {
            return null;
        }
        return $funcCall->args[$index]->value;
    }
    private function createCarbonNowFormat(String_ $string) : MethodCall
    {
        return new MethodCall($this->createCarbonNow(), 'format', [new Arg($string)]);
    }
    private function createCarbonNow() : StaticCall
    {
        return new StaticCall(new FullyQualified('Carbon\\Carbon'), 'now');
    }
    private function createCarbonParseTimestamp(Expr $dateExpr) : MethodCall
    {
        $staticCall = new StaticCall(new FullyQualified('Carbon\\Carbon'), 'parse', [new Arg($dateExpr)]);
        return new MethodCall($staticCall, 'getTimestamp');
    }
    private function createCarbonParseFormat(Expr $dateExpr, Expr $format) : MethodCall
    {
        $staticCall = new StaticCall(new FullyQualified('Carbon\\Carbon'), 'parse', [new Arg($dateExpr)]);
        return new MethodCall($staticCall, 'format', [new Arg($format)]);
    }
    private function createCarbonFromTimestamp(Expr $timestampExpr, Expr $format) : MethodCall
    {
        $staticCall = new StaticCall(new FullyQualified('Carbon\\Carbon'), 'createFromTimestamp', [new Arg($timestampExpr)]);
        return new MethodCall($staticCall, 'format', [new Arg($format)]);
    }
    /**
     * @param array{unit: string, value: int} $timeUnit
     */
    private function createCarbonSubtract(array $timeUnit) : MethodCall
    {
        $staticCall = new StaticCall(new FullyQualified('Carbon\\Carbon'), 'now');
        $methodName = 'sub' . \ucfirst($timeUnit['unit']);
        $methodCall = new MethodCall($staticCall, $methodName, [new Arg(new LNumber($timeUnit['value']))]);
        return new MethodCall($methodCall, 'getTimestamp');
    }
    /**
     * @return array{unit: string, value: int}|null
     */
    private function detectTimeUnit(Expr $expr) : ?array
    {
        $product = $this->calculateProduct($expr);
        if ($product === null) {
            return null;
        }
        foreach (self::TIME_UNITS as [$unit, $seconds]) {
            if ($product % $seconds === 0) {
                return ['unit' => (string) $unit, 'value' => (int) ($product / $seconds)];
            }
        }
        return null;
    }
    /**
     * @return float|int|null
     */
    private function calculateProduct(Expr $expr)
    {
        if ($expr instanceof LNumber) {
            return $expr->value;
        }
        if (!$expr instanceof Mul) {
            return null;
        }
        $multipliers = $this->extractMultipliers($expr);
        if ($multipliers === []) {
            return null;
        }
        return \array_product($multipliers);
    }
    /**
     * @return int[]
     */
    private function extractMultipliers(Node $node) : array
    {
        $multipliers = [];
        if (!$node instanceof Mul) {
            return $multipliers;
        }
        if ($node->left instanceof LNumber) {
            $multipliers[] = $node->left->value;
        } elseif ($node->left instanceof Mul) {
            $multipliers = \array_merge($multipliers, $this->extractMultipliers($node->left));
        }
        if ($node->right instanceof LNumber) {
            $multipliers[] = $node->right->value;
        } elseif ($node->right instanceof Mul) {
            $multipliers = \array_merge($multipliers, $this->extractMultipliers($node->right));
        }
        return $multipliers;
    }
}
