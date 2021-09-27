<?php

declare (strict_types=1);
namespace Rector\DowngradePhp73\Rector\FuncCall;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DowngradePhp73\Rector\FuncCall\SetCookieOptionsArrayToArgumentsRector\SetCookieOptionsArrayToArgumentsRectorTest
 */
final class SetCookieOptionsArrayToArgumentsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * Conversion table from argument index to options name
     * @var array<string, int>
     */
    private const ARGUMENT_ORDER = ['expires' => 2, 'path' => 3, 'domain' => 4, 'secure' => 5, 'httponly' => 6];
    /**
     * Conversion table from argument index to options name
     * @var array<int, int|string|bool>
     */
    private const ARGUMENT_DEFAULT_VALUES = [2 => 0, 3 => '', 4 => '', 5 => \false, 6 => \false];
    /**
     * @var int
     */
    private $highestIndex = 1;
    /**
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert setcookie option array to arguments', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
setcookie('name', $value, ['expires' => 360]);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
setcookie('name', $value, 360);
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\FuncCall::class];
    }
    /**
     * @param FuncCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $node->args = $this->composeNewArgs($node);
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        if (!$this->isNames($funcCall, ['setcookie', 'setrawcookie'])) {
            return \true;
        }
        $argsCount = \count($funcCall->args);
        if ($argsCount <= 2) {
            return \true;
        }
        if (!isset($funcCall->args[2])) {
            return \true;
        }
        return !($funcCall->args[2] instanceof \PhpParser\Node\Arg && $funcCall->args[2]->value instanceof \PhpParser\Node\Expr\Array_);
    }
    /**
     * @return Arg[]
     */
    private function composeNewArgs(\PhpParser\Node\Expr\FuncCall $funcCall) : array
    {
        $this->highestIndex = 1;
        if (!$this->argsAnalyzer->isArgsInstanceInArgsPositions($funcCall->args, [0, 1, 2])) {
            return [];
        }
        /** @var Arg $firstArg */
        $firstArg = $funcCall->args[0];
        /** @var Arg $secondArg */
        $secondArg = $funcCall->args[1];
        $newArgs = [$firstArg, $secondArg];
        /** @var Arg $thirdArg */
        $thirdArg = $funcCall->args[2];
        /** @var Array_ $optionsArray */
        $optionsArray = $thirdArg->value;
        /** @var ArrayItem|null $arrayItem */
        foreach ($optionsArray->items as $arrayItem) {
            if ($arrayItem === null) {
                continue;
            }
            $value = $arrayItem->value;
            /** @var String_ $key */
            $key = $arrayItem->key;
            $name = $key->value;
            if (!$this->isMappableArrayKey($name)) {
                continue;
            }
            $order = self::ARGUMENT_ORDER[$name];
            if ($order > $this->highestIndex) {
                $this->highestIndex = $order;
            }
            $newArgs[$order] = new \PhpParser\Node\Arg($value);
        }
        $newArgs = $this->fillMissingArgumentsWithDefaultValues($newArgs);
        \ksort($newArgs);
        return $newArgs;
    }
    private function isMappableArrayKey(string $key) : bool
    {
        return isset(self::ARGUMENT_ORDER[$key]);
    }
    /**
     * @param Arg[] $args
     * @return Arg[]
     */
    private function fillMissingArgumentsWithDefaultValues(array $args) : array
    {
        for ($i = 1; $i < $this->highestIndex; ++$i) {
            if (isset($args[$i])) {
                continue;
            }
            $args[$i] = $this->createDefaultValueArg($i);
        }
        return $args;
    }
    private function createDefaultValueArg(int $argumentIndex) : \PhpParser\Node\Arg
    {
        if (!\array_key_exists($argumentIndex, self::ARGUMENT_DEFAULT_VALUES)) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $argumentDefaultValue = self::ARGUMENT_DEFAULT_VALUES[$argumentIndex];
        $expr = \PhpParser\BuilderHelpers::normalizeValue($argumentDefaultValue);
        return new \PhpParser\Node\Arg($expr);
    }
}
