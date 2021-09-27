<?php

declare (strict_types=1);
namespace Rector\Php70\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use Rector\Core\NodeAnalyzer\ArgsAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php70\EregToPcreTransformer;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog http://php.net/reference.pcre.pattern.posix https://stackoverflow.com/a/17033826/1348344 https://docstore.mik.ua/orelly/webprog/pcook/ch13_02.htm
 *
 * @see \Rector\Tests\Php70\Rector\FuncCall\EregToPregMatchRector\EregToPregMatchRectorTest
 */
final class EregToPregMatchRector extends \Rector\Core\Rector\AbstractRector implements \Rector\VersionBonding\Contract\MinPhpVersionInterface
{
    /**
     * @var array<string, string>
     */
    private const OLD_NAMES_TO_NEW_ONES = ['ereg' => 'preg_match', 'eregi' => 'preg_match', 'ereg_replace' => 'preg_replace', 'eregi_replace' => 'preg_replace', 'split' => 'preg_split', 'spliti' => 'preg_split'];
    /**
     * @var \Rector\Php70\EregToPcreTransformer
     */
    private $eregToPcreTransformer;
    /**
     * @var \Rector\Core\NodeAnalyzer\ArgsAnalyzer
     */
    private $argsAnalyzer;
    public function __construct(\Rector\Php70\EregToPcreTransformer $eregToPcreTransformer, \Rector\Core\NodeAnalyzer\ArgsAnalyzer $argsAnalyzer)
    {
        $this->eregToPcreTransformer = $eregToPcreTransformer;
        $this->argsAnalyzer = $argsAnalyzer;
    }
    public function provideMinPhpVersion() : int
    {
        return \Rector\Core\ValueObject\PhpVersionFeature::NO_EREG_FUNCTION;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes ereg*() to preg*() calls', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('ereg("hi")', 'preg_match("#hi#");')]);
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
        /** @var string $functionName */
        $functionName = $this->getName($node);
        /** @var Arg $firstArg */
        $firstArg = $node->args[0];
        $patternNode = $firstArg->value;
        if ($patternNode instanceof \PhpParser\Node\Scalar\String_) {
            $this->processStringPattern($node, $patternNode, $functionName);
        } elseif ($patternNode instanceof \PhpParser\Node\Expr\Variable) {
            $this->processVariablePattern($node, $patternNode, $functionName);
        }
        $this->processSplitLimitArgument($node, $functionName);
        $node->name = new \PhpParser\Node\Name(self::OLD_NAMES_TO_NEW_ONES[$functionName]);
        // ereg|eregi 3rd argument return value fix
        if (\in_array($functionName, ['ereg', 'eregi'], \true) && isset($node->args[2]) && $node->args[2] instanceof \PhpParser\Node\Arg) {
            $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
            if ($parentNode instanceof \PhpParser\Node\Expr\Assign) {
                return $this->createTernaryWithStrlenOfFirstMatch($node);
            }
        }
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Expr\FuncCall $funcCall) : bool
    {
        $functionName = $this->getName($funcCall);
        if ($functionName === null) {
            return \true;
        }
        if (!isset(self::OLD_NAMES_TO_NEW_ONES[$functionName])) {
            return \true;
        }
        return !$this->argsAnalyzer->isArgInstanceInArgsPosition($funcCall->args, 0);
    }
    private function processStringPattern(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Scalar\String_ $string, string $functionName) : void
    {
        $pattern = $string->value;
        $pattern = $this->eregToPcreTransformer->transform($pattern, $this->isCaseInsensitiveFunction($functionName));
        /** @var Arg $arg */
        $arg = $funcCall->args[0];
        $arg->value = new \PhpParser\Node\Scalar\String_($pattern);
    }
    private function processVariablePattern(\PhpParser\Node\Expr\FuncCall $funcCall, \PhpParser\Node\Expr\Variable $variable, string $functionName) : void
    {
        $pregQuotePatternNode = $this->nodeFactory->createFuncCall('preg_quote', [new \PhpParser\Node\Arg($variable), new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_('#'))]);
        $startConcat = new \PhpParser\Node\Expr\BinaryOp\Concat(new \PhpParser\Node\Scalar\String_('#'), $pregQuotePatternNode);
        $endDelimiter = $this->isCaseInsensitiveFunction($functionName) ? '#mi' : '#m';
        $concat = new \PhpParser\Node\Expr\BinaryOp\Concat($startConcat, new \PhpParser\Node\Scalar\String_($endDelimiter));
        /** @var Arg $arg */
        $arg = $funcCall->args[0];
        $arg->value = $concat;
    }
    /**
     * Equivalent of:
     * split(' ', 'hey Tom', 0);
     * ↓
     * preg_split('# #', 'hey Tom', 1);
     */
    private function processSplitLimitArgument(\PhpParser\Node\Expr\FuncCall $funcCall, string $functionName) : void
    {
        if (!isset($funcCall->args[2])) {
            return;
        }
        if (!$funcCall->args[2] instanceof \PhpParser\Node\Arg) {
            return;
        }
        if (\strncmp($functionName, 'split', \strlen('split')) !== 0) {
            return;
        }
        // 3rd argument - $limit, 0 → 1
        if (!isset($funcCall->args[2])) {
            return;
        }
        if (!$funcCall->args[2]->value instanceof \PhpParser\Node\Scalar\LNumber) {
            return;
        }
        /** @var LNumber $limitNumberNode */
        $limitNumberNode = $funcCall->args[2]->value;
        if ($limitNumberNode->value !== 0) {
            return;
        }
        $limitNumberNode->value = 1;
    }
    private function createTernaryWithStrlenOfFirstMatch(\PhpParser\Node\Expr\FuncCall $funcCall) : \PhpParser\Node\Expr\Ternary
    {
        /** @var Arg $thirdArg */
        $thirdArg = $funcCall->args[2];
        $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($thirdArg->value, new \PhpParser\Node\Scalar\LNumber(0));
        $strlenFuncCall = $this->nodeFactory->createFuncCall('strlen', [$arrayDimFetch]);
        return new \PhpParser\Node\Expr\Ternary($funcCall, $strlenFuncCall, $this->nodeFactory->createFalse());
    }
    private function isCaseInsensitiveFunction(string $functionName) : bool
    {
        if (\strpos($functionName, 'eregi') !== \false) {
            return \true;
        }
        return \strpos($functionName, 'spliti') !== \false;
    }
}
