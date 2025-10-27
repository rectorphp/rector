<?php

declare (strict_types=1);
namespace Rector\PhpParser\Printer;

use RectorPrefix202510\Nette\Utils\Strings;
use PhpParser\Comment;
use PhpParser\Internal\TokenStream;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp;
use PhpParser\Node\Expr\BinaryOp\Pipe;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Nop;
use PhpParser\PrettyPrinter\Standard;
use PhpParser\Token;
use PHPStan\Node\AnonymousClassNode;
use PHPStan\Node\Expr\AlwaysRememberedExpr;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\NodeAnalyzer\ExprAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Util\NewLineSplitter;
use Rector\Util\Reflection\PrivatesAccessor;
use Rector\Util\StringUtils;
/**
 * @see \Rector\Tests\PhpParser\Printer\BetterStandardPrinterTest
 *
 * @property array<string, array{string, bool, string, null}> $insertionMap
 */
final class BetterStandardPrinter extends Standard
{
    /**
     * @readonly
     */
    private ExprAnalyzer $exprAnalyzer;
    /**
     * @readonly
     */
    private PrivatesAccessor $privatesAccessor;
    /**
     * Remove extra spaces before new Nop_ nodes
     * @see https://regex101.com/r/iSvroO/1
     * @var string
     */
    private const EXTRA_SPACE_BEFORE_NOP_REGEX = '#^[ \t]+$#m';
    /**
     * @see https://regex101.com/r/UluSYL/1
     * @var string
     */
    private const SPACED_NEW_START_REGEX = '#^new\s+#';
    public function __construct(ExprAnalyzer $exprAnalyzer, PrivatesAccessor $privatesAccessor)
    {
        $this->exprAnalyzer = $exprAnalyzer;
        $this->privatesAccessor = $privatesAccessor;
        parent::__construct();
    }
    /**
     * @param Node[] $stmts
     * @param Node[] $origStmts
     * @param mixed[] $origTokens
     */
    public function printFormatPreserving(array $stmts, array $origStmts, array $origTokens): string
    {
        $newStmts = $this->resolveNewStmts($stmts);
        $content = parent::printFormatPreserving($newStmts, $origStmts, $origTokens);
        // add new line in case of added stmts
        if (count($newStmts) !== count($origStmts) && substr_compare($content, "\n", -strlen("\n")) !== 0) {
            $content .= $this->nl;
        }
        return $content;
    }
    /**
     * @param Node|Node[]|null $node
     */
    public function print($node): string
    {
        if ($node === null) {
            $node = [];
        }
        if (!is_array($node)) {
            $node = [$node];
        }
        return $this->prettyPrint($node);
    }
    /**
     * @param Node[] $stmts
     */
    public function prettyPrintFile(array $stmts): string
    {
        // to keep indexes from 0
        $stmts = array_values($stmts);
        return parent::prettyPrintFile($stmts) . \PHP_EOL;
    }
    /**
     * @api magic method in parent
     */
    public function pFileWithoutNamespace(FileWithoutNamespace $fileWithoutNamespace): string
    {
        return $this->pStmts($fileWithoutNamespace->stmts);
    }
    /**
     * @api magic method in parent
     */
    public function pInterpolatedStringPart(InterpolatedStringPart $interpolatedStringPart): string
    {
        return $interpolatedStringPart->value;
    }
    protected function p(Node $node, int $precedence = self::MAX_PRECEDENCE, int $lhsPrecedence = self::MAX_PRECEDENCE, bool $parentFormatPreserved = \false): string
    {
        // handle already AlwaysRememberedExpr
        // @see https://github.com/rectorphp/rector/issues/8815#issuecomment-2503453191
        while ($node instanceof AlwaysRememberedExpr) {
            $node = $node->getExpr();
        }
        // handle overlapped origNode is Match_
        // and its subnodes still have AlwaysRememberedExpr
        $originalNode = $node->getAttribute(AttributeKey::ORIGINAL_NODE);
        if ($originalNode instanceof Match_) {
            $subNodeNames = $node->getSubNodeNames();
            foreach ($subNodeNames as $subNodeName) {
                while ($originalNode->{$subNodeName} instanceof AlwaysRememberedExpr) {
                    $originalNode->{$subNodeName} = $originalNode->{$subNodeName}->getExpr();
                }
            }
        }
        $this->wrapBinaryOp($node);
        $content = parent::p($node, $precedence, $lhsPrecedence, $parentFormatPreserved);
        /** @todo remove once fix https://github.com/nikic/PHP-Parser/commit/232169fd7972e018e3d7adbcaa235a2eaa2440c4 is released */
        if ($node instanceof New_ && $node->class instanceof AnonymousClassNode && !StringUtils::isMatch($content, self::SPACED_NEW_START_REGEX)) {
            $content = 'new ' . $content;
        }
        if ($node instanceof CallLike) {
            $this->cleanVariadicPlaceHolderTrailingComma($node);
        }
        return $node->getAttribute(AttributeKey::WRAPPED_IN_PARENTHESES) === \true ? '(' . $content . ')' : $content;
    }
    protected function pExpr_ArrowFunction(ArrowFunction $arrowFunction, int $precedence, int $lhsPrecedence): string
    {
        if (!$arrowFunction->hasAttribute(AttributeKey::COMMENT_CLOSURE_RETURN_MIRRORED)) {
            return parent::pExpr_ArrowFunction($arrowFunction, $precedence, $lhsPrecedence);
        }
        $expr = $arrowFunction->expr;
        /** @var Comment[] $comments */
        $comments = $expr->getAttribute(AttributeKey::COMMENTS) ?? [];
        if ($comments === []) {
            return parent::pExpr_ArrowFunction($arrowFunction, $precedence, $lhsPrecedence);
        }
        $indent = $this->resolveIndentSpaces();
        $text = "\n" . $indent;
        foreach ($comments as $key => $comment) {
            $commentText = $key > 0 ? $indent . $comment->getText() : $comment->getText();
            $text .= $commentText . "\n";
        }
        return $this->pPrefixOp(ArrowFunction::class, $this->pAttrGroups($arrowFunction->attrGroups, \true) . $this->pStatic($arrowFunction->static) . 'fn' . ($arrowFunction->byRef ? '&' : '') . '(' . $this->pMaybeMultiline($arrowFunction->params, $this->phpVersion->supportsTrailingCommaInParamList()) . ')' . ($arrowFunction->returnType instanceof Node ? ': ' . $this->p($arrowFunction->returnType) : '') . ' =>' . $text . $indent, $arrowFunction->expr, $precedence, $lhsPrecedence);
    }
    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function setIndentLevel(int $level): void
    {
        $level = max($level, 0);
        $this->indentLevel = $level;
        $this->nl = "\n" . str_repeat($this->getIndentCharacter(), $level);
    }
    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function indent(): void
    {
        $indentSize = SimpleParameterProvider::provideIntParameter(Option::INDENT_SIZE);
        $this->indentLevel += $indentSize;
        $this->nl .= str_repeat($this->getIndentCharacter(), $indentSize);
    }
    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function outdent(): void
    {
        if ($this->getIndentCharacter() === ' ') {
            $indentSize = SimpleParameterProvider::provideIntParameter(Option::INDENT_SIZE);
            assert($this->indentLevel >= $indentSize);
            $this->indentLevel -= $indentSize;
        } else {
            // - 1 tab
            assert($this->indentLevel >= 1);
            --$this->indentLevel;
        }
        $this->nl = "\n" . str_repeat($this->getIndentCharacter(), $this->indentLevel);
    }
    /**
     * @param mixed[] $nodes
     * @param mixed[] $origNodes
     */
    protected function pArray(array $nodes, array $origNodes, int &$pos, int $indentAdjustment, string $parentNodeClass, string $subNodeName, ?int $fixup): ?string
    {
        // reindex positions for printer
        $nodes = array_values($nodes);
        $content = parent::pArray($nodes, $origNodes, $pos, $indentAdjustment, $parentNodeClass, $subNodeName, $fixup);
        if ($content === null) {
            return $content;
        }
        if (!$this->containsNop($nodes)) {
            return $content;
        }
        return Strings::replace($content, self::EXTRA_SPACE_BEFORE_NOP_REGEX);
    }
    /**
     * Do not add "()" on Expressions
     * @see https://github.com/rectorphp/rector/pull/401#discussion_r181487199
     */
    protected function pExpr_Yield(Yield_ $yield, int $precedence, int $lhsPrecedence): string
    {
        if (!$yield->value instanceof Expr) {
            return 'yield';
        }
        // brackets are needed only in case of assign, @see https://www.php.net/manual/en/language.generators.syntax.php
        $shouldAddBrackets = (bool) $yield->getAttribute(AttributeKey::IS_ASSIGNED_TO);
        return sprintf('%syield %s%s%s', $shouldAddBrackets ? '(' : '', $yield->key instanceof Expr ? $this->p($yield->key) . ' => ' : '', $this->p($yield->value), $shouldAddBrackets ? ')' : '');
    }
    /**
     * Print new lined array items when newlined_array_print is set to true
     */
    protected function pExpr_Array(Array_ $array): string
    {
        if ($array->getAttribute(AttributeKey::NEWLINED_ARRAY_PRINT) === \true) {
            $printedArray = '[';
            $printedArray .= $this->pCommaSeparatedMultiline($array->items, \true);
            return $printedArray . ($this->nl . ']');
        }
        return parent::pExpr_Array($array);
    }
    protected function pExpr_BinaryOp_Pipe(Pipe $node, int $precedence, int $lhsPrecedence): string
    {
        return $this->pInfixOp(Pipe::class, $node->left, "\n" . $this->resolveIndentSpaces() . '|> ', $node->right, $precedence, $lhsPrecedence);
    }
    /**
     * Fixes escaping of regular patterns
     */
    protected function pScalar_String(String_ $string): string
    {
        if ($string->getAttribute(AttributeKey::DOC_INDENTATION) === '__REMOVED__') {
            $content = parent::pScalar_String($string);
            return $this->cleanStartIndentationOnHeredocNowDoc($content);
        }
        $isRegularPattern = (bool) $string->getAttribute(AttributeKey::IS_REGULAR_PATTERN, \false);
        if (!$isRegularPattern) {
            return parent::pScalar_String($string);
        }
        $kind = $string->getAttribute(AttributeKey::KIND, String_::KIND_SINGLE_QUOTED);
        if ($kind === String_::KIND_DOUBLE_QUOTED) {
            return '"' . $string->value . '"';
        }
        if ($kind === String_::KIND_SINGLE_QUOTED) {
            return "'" . $string->value . "'";
        }
        return parent::pScalar_String($string);
    }
    /**
     * It remove all spaces extra to parent
     */
    protected function pStmt_Declare(Declare_ $declare): string
    {
        $declareString = parent::pStmt_Declare($declare);
        return Strings::replace($declareString, '#\s+#');
    }
    protected function pExpr_Ternary(Ternary $ternary, int $precedence, int $lhsPrecedence): string
    {
        $kind = $ternary->getAttribute(AttributeKey::KIND);
        if ($kind === AttributeKey::WRAPPED_IN_PARENTHESES) {
            $pExprTernary = parent::pExpr_Ternary($ternary, $precedence, $lhsPrecedence);
            return '(' . $pExprTernary . ')';
        }
        return parent::pExpr_Ternary($ternary, $precedence, $lhsPrecedence);
    }
    /**
     * Used in rector-downgrade-php
     */
    protected function pScalar_InterpolatedString(InterpolatedString $interpolatedString): string
    {
        $content = parent::pScalar_InterpolatedString($interpolatedString);
        if ($interpolatedString->getAttribute(AttributeKey::DOC_INDENTATION) === '__REMOVED__') {
            return $this->cleanStartIndentationOnHeredocNowDoc($content);
        }
        return $content;
    }
    protected function pExpr_MethodCall(MethodCall $methodCall): string
    {
        if (!$methodCall->var instanceof CallLike) {
            return parent::pExpr_MethodCall($methodCall);
        }
        if (SimpleParameterProvider::provideBoolParameter(Option::NEW_LINE_ON_FLUENT_CALL) === \false) {
            return parent::pExpr_MethodCall($methodCall);
        }
        foreach ($methodCall->args as $arg) {
            if (!$arg instanceof Arg) {
                continue;
            }
            $arg->value->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        return $this->pDereferenceLhs($methodCall->var) . "\n" . $this->resolveIndentSpaces() . '->' . $this->pObjectProperty($methodCall->name) . '(' . $this->pMaybeMultiline($methodCall->args) . ')';
    }
    protected function pInfixOp(string $class, Node $leftNode, string $operatorString, Node $rightNode, int $precedence, int $lhsPrecedence): string
    {
        $this->wrapAssign($leftNode, $rightNode);
        return parent::pInfixOp($class, $leftNode, $operatorString, $rightNode, $precedence, $lhsPrecedence);
    }
    protected function pExpr_Instanceof(Instanceof_ $instanceof, int $precedence, int $lhsPrecedence): string
    {
        $this->wrapAssign($instanceof->expr, $instanceof->class);
        return parent::pExpr_Instanceof($instanceof, $precedence, $lhsPrecedence);
    }
    /**
     * @todo remove once https://github.com/nikic/PHP-Parser/pull/1125 is merged and released
     */
    private function cleanVariadicPlaceHolderTrailingComma(CallLike $callLike): void
    {
        $originalNode = $callLike->getAttribute(AttributeKey::ORIGINAL_NODE);
        if (!$originalNode instanceof CallLike) {
            return;
        }
        if ($originalNode->isFirstClassCallable()) {
            return;
        }
        if (!$callLike->isFirstClassCallable()) {
            return;
        }
        if (!$this->origTokens instanceof TokenStream) {
            return;
        }
        /** @var Token[] $tokens */
        $tokens = $this->privatesAccessor->getPrivateProperty($this->origTokens, 'tokens');
        $iteration = 1;
        while (isset($tokens[$callLike->getEndTokenPos() - $iteration])) {
            $text = trim((string) $tokens[$callLike->getEndTokenPos() - $iteration]->text);
            if (in_array($text, [')', ''], \true)) {
                ++$iteration;
                continue;
            }
            if ($text === ',') {
                $tokens[$callLike->getEndTokenPos() - $iteration]->text = '';
            }
            break;
        }
    }
    private function wrapBinaryOp(Node $node): void
    {
        if ($this->exprAnalyzer->isExprWithExprPropertyWrappable($node)) {
            $node->expr->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        if (!$node instanceof BinaryOp) {
            return;
        }
        if ($node->getAttribute(AttributeKey::ORIGINAL_NODE) instanceof Node) {
            return;
        }
        if ($node->left instanceof Assign && $this->origTokens instanceof TokenStream && !$this->origTokens->haveParens($node->left->getStartTokenPos(), $node->left->getEndTokenPos())) {
            $node->left->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        if ($node->left instanceof BinaryOp && $node->left->getAttribute(AttributeKey::ORIGINAL_NODE) instanceof Node) {
            $node->left->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
        if ($node->right instanceof BinaryOp && $node->right->getAttribute(AttributeKey::ORIGINAL_NODE) instanceof Node) {
            $node->right->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        }
    }
    /**
     * ensure left side is assign and right side is just created
     *
     * @see https://github.com/rectorphp/rector-src/pull/6668
     * @see https://github.com/rectorphp/rector/issues/8980
     * @see https://github.com/rectorphp/rector-src/pull/6653
     */
    private function wrapAssign(Node $leftNode, Node $rightNode): void
    {
        if ($leftNode instanceof Assign && $leftNode->getStartTokenPos() > 0 && $rightNode->getStartTokenPos() < 0) {
            $leftNode->setAttribute(AttributeKey::WRAPPED_IN_PARENTHESES, \true);
        }
    }
    private function cleanStartIndentationOnHeredocNowDoc(string $content): string
    {
        $lines = NewLineSplitter::split($content);
        $trimmedLines = array_map(\Closure::fromCallable('ltrim'), $lines);
        return implode("\n", $trimmedLines);
    }
    private function resolveIndentSpaces(): string
    {
        $indentSize = SimpleParameterProvider::provideIntParameter(Option::INDENT_SIZE);
        return str_repeat($this->getIndentCharacter(), $this->indentLevel) . str_repeat($this->getIndentCharacter(), $indentSize);
    }
    /**
     * Must be a method to be able to react to changed parameter in tests
     */
    private function getIndentCharacter(): string
    {
        return SimpleParameterProvider::provideStringParameter(Option::INDENT_CHAR, ' ');
    }
    /**
     * @param Node[] $stmts
     * @return Node[]|mixed[]
     */
    private function resolveNewStmts(array $stmts): array
    {
        $stmts = array_values($stmts);
        if (count($stmts) === 1 && $stmts[0] instanceof FileWithoutNamespace) {
            return array_values($stmts[0]->stmts);
        }
        return $stmts;
    }
    /**
     * @param Node[] $nodes
     */
    private function containsNop(array $nodes): bool
    {
        $hasNop = \false;
        foreach ($nodes as $node) {
            // early false when visited Node is InlineHTML
            if ($node instanceof InlineHTML) {
                return \false;
            }
            // use flag to avoid next is InlineHTML that returns early
            if ($node instanceof Nop) {
                $hasNop = \true;
            }
        }
        return $hasNop;
    }
}
