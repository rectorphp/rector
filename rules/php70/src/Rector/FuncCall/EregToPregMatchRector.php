<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\FuncCall;

use Nette\Utils\Strings;
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
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php70\EregToPcreTransformer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see http://php.net/reference.pcre.pattern.posix
 * @see https://stackoverflow.com/a/17033826/1348344
 * @see https://docstore.mik.ua/orelly/webprog/pcook/ch13_02.htm
 *
 * @see \Rector\Php70\Tests\Rector\FuncCall\EregToPregMatchRector\EregToPregMatchRectorTest
 */
final class EregToPregMatchRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const OLD_NAMES_TO_NEW_ONES = [
        'ereg' => 'preg_match',
        'eregi' => 'preg_match',
        'ereg_replace' => 'preg_replace',
        'eregi_replace' => 'preg_replace',
        'split' => 'preg_split',
        'spliti' => 'preg_split',
    ];

    /**
     * @var EregToPcreTransformer
     */
    private $eregToPcreTransformer;

    public function __construct(EregToPcreTransformer $eregToPcreTransformer)
    {
        $this->eregToPcreTransformer = $eregToPcreTransformer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Changes ereg*() to preg*() calls',
            [new CodeSample('ereg("hi")', 'preg_match("#hi#");')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $functionName = $this->getName($node);
        if ($functionName === null) {
            return null;
        }

        if (! isset(self::OLD_NAMES_TO_NEW_ONES[$functionName])) {
            return null;
        }

        $patternNode = $node->args[0]->value;
        if ($patternNode instanceof String_) {
            $this->processStringPattern($node, $patternNode, $functionName);
        } elseif ($patternNode instanceof Variable) {
            $this->processVariablePattern($node, $patternNode, $functionName);
        }

        $this->processSplitLimitArgument($node, $functionName);

        $node->name = new Name(self::OLD_NAMES_TO_NEW_ONES[$functionName]);

        // ereg|eregi 3rd argument return value fix
        if (in_array($functionName, ['ereg', 'eregi'], true) && isset($node->args[2])) {
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentNode instanceof Assign) {
                return $this->createTernaryWithStrlenOfFirstMatch($node);
            }
        }

        return $node;
    }

    private function processStringPattern(FuncCall $funcCall, String_ $string, string $functionName): void
    {
        $pattern = $string->value;
        $pattern = $this->eregToPcreTransformer->transform($pattern, $this->isCaseInsensitiveFunction($functionName));

        $funcCall->args[0]->value = new String_($pattern);
    }

    private function processVariablePattern(FuncCall $funcCall, Variable $variable, string $functionName): void
    {
        $pregQuotePatternNode = $this->nodeFactory->createFuncCall('preg_quote', [
            new Arg($variable),
            new Arg(new String_('#')),
        ]);

        $startConcat = new Concat(new String_('#'), $pregQuotePatternNode);

        $endDelimiter = $this->isCaseInsensitiveFunction($functionName) ? '#mi' : '#m';
        $concat = new Concat($startConcat, new String_($endDelimiter));

        $funcCall->args[0]->value = $concat;
    }

    /**
     * Equivalent of:
     * split(' ', 'hey Tom', 0);
     * ↓
     * preg_split('# #', 'hey Tom', 1);
     */
    private function processSplitLimitArgument(FuncCall $funcCall, string $functionName): void
    {
        if (! Strings::startsWith($functionName, 'split')) {
            return;
        }

        // 3rd argument - $limit, 0 → 1
        if (! isset($funcCall->args[2])) {
            return;
        }

        if (! $funcCall->args[2]->value instanceof LNumber) {
            return;
        }

        /** @var LNumber $limitNumberNode */
        $limitNumberNode = $funcCall->args[2]->value;
        if ($limitNumberNode->value !== 0) {
            return;
        }

        $limitNumberNode->value = 1;
    }

    private function createTernaryWithStrlenOfFirstMatch(FuncCall $funcCall): Ternary
    {
        $arrayDimFetch = new ArrayDimFetch($funcCall->args[2]->value, new LNumber(0));
        $strlenFuncCall = $this->nodeFactory->createFuncCall('strlen', [$arrayDimFetch]);

        return new Ternary($funcCall, $strlenFuncCall, $this->nodeFactory->createFalse());
    }

    private function isCaseInsensitiveFunction(string $functionName): bool
    {
        if (Strings::contains($functionName, 'eregi')) {
            return true;
        }
        return Strings::contains($functionName, 'spliti');
    }
}
