<?php

declare (strict_types=1);
namespace Rector\PhpParser\Printer;

use RectorPrefix202412\Nette\Utils\Strings;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\CallLike;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\InterpolatedStringPart;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\Float_;
use PhpParser\Node\Scalar\Int_;
use PhpParser\Node\Scalar\InterpolatedString;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Nop;
use PhpParser\PrettyPrinter\Standard;
use PHPStan\Node\Expr\AlwaysRememberedExpr;
use Rector\Configuration\Option;
use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Util\NewLineSplitter;
/**
 * @see \Rector\Tests\PhpParser\Printer\BetterStandardPrinterTest
 *
 * @property array<string, array{string, bool, string, null}> $insertionMap
 */
final class BetterStandardPrinter extends Standard
{
    /**
     * Remove extra spaces before new Nop_ nodes
     * @see https://regex101.com/r/iSvroO/1
     * @var string
     */
    private const EXTRA_SPACE_BEFORE_NOP_REGEX = '#^[ \\t]+$#m';
    /**
     * @param Node[] $stmts
     * @param Node[] $origStmts
     * @param mixed[] $origTokens
     */
    public function printFormatPreserving(array $stmts, array $origStmts, array $origTokens) : string
    {
        $newStmts = $this->resolveNewStmts($stmts);
        $content = parent::printFormatPreserving($newStmts, $origStmts, $origTokens);
        // add new line in case of added stmts
        if (\count($newStmts) !== \count($origStmts) && \substr_compare($content, "\n", -\strlen("\n")) !== 0) {
            $content .= $this->nl;
        }
        return $content;
    }
    /**
     * @param Node|Node[]|null $node
     */
    public function print($node) : string
    {
        if ($node === null) {
            $node = [];
        }
        if (!\is_array($node)) {
            $node = [$node];
        }
        return $this->prettyPrint($node);
    }
    /**
     * @param Node[] $stmts
     */
    public function prettyPrintFile(array $stmts) : string
    {
        // to keep indexes from 0
        $stmts = \array_values($stmts);
        return parent::prettyPrintFile($stmts) . \PHP_EOL;
    }
    /**
     * @api magic method in parent
     */
    public function pFileWithoutNamespace(FileWithoutNamespace $fileWithoutNamespace) : string
    {
        return $this->pStmts($fileWithoutNamespace->stmts);
    }
    /**
     * @api magic method in parent
     */
    public function pInterpolatedStringPart(InterpolatedStringPart $interpolatedStringPart) : string
    {
        return $interpolatedStringPart->value;
    }
    protected function p(Node $node, int $precedence = self::MAX_PRECEDENCE, int $lhsPrecedence = self::MAX_PRECEDENCE, bool $parentFormatPreserved = \false) : string
    {
        while ($node instanceof AlwaysRememberedExpr) {
            $node = $node->getExpr();
        }
        $content = parent::p($node, $precedence, $lhsPrecedence, $parentFormatPreserved);
        return $node->getAttribute(AttributeKey::WRAPPED_IN_PARENTHESES) === \true ? '(' . $content . ')' : $content;
    }
    protected function pAttributeGroup(AttributeGroup $attributeGroup) : string
    {
        $ret = parent::pAttributeGroup($attributeGroup);
        $comment = $attributeGroup->getAttribute(AttributeKey::ATTRIBUTE_COMMENT);
        if (!\in_array($comment, ['', null], \true)) {
            $ret .= ' // ' . $comment;
        }
        return $ret;
    }
    protected function pExpr_ArrowFunction(ArrowFunction $arrowFunction, int $precedence, int $lhsPrecedence) : string
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
    protected function setIndentLevel(int $level) : void
    {
        $level = \max($level, 0);
        $this->indentLevel = $level;
        $this->nl = "\n" . \str_repeat($this->getIndentCharacter(), $level);
    }
    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function indent() : void
    {
        $indentSize = SimpleParameterProvider::provideIntParameter(Option::INDENT_SIZE);
        $this->indentLevel += $indentSize;
        $this->nl .= \str_repeat($this->getIndentCharacter(), $indentSize);
    }
    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function outdent() : void
    {
        if ($this->getIndentCharacter() === ' ') {
            $indentSize = SimpleParameterProvider::provideIntParameter(Option::INDENT_SIZE);
            \assert($this->indentLevel >= $indentSize);
            $this->indentLevel -= $indentSize;
        } else {
            // - 1 tab
            \assert($this->indentLevel >= 1);
            --$this->indentLevel;
        }
        $this->nl = "\n" . \str_repeat($this->getIndentCharacter(), $this->indentLevel);
    }
    /**
     * @param mixed[] $nodes
     * @param mixed[] $origNodes
     */
    protected function pArray(array $nodes, array $origNodes, int &$pos, int $indentAdjustment, string $parentNodeClass, string $subNodeName, ?int $fixup) : ?string
    {
        // reindex positions for printer
        $nodes = \array_values($nodes);
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
     * Emulates 1_000 in PHP 7.3- version
     */
    protected function pScalar_Float(Float_ $float) : string
    {
        if ($this->shouldPrintNewRawValue($float)) {
            return (string) $float->getAttribute(AttributeKey::RAW_VALUE);
        }
        return parent::pScalar_Float($float);
    }
    /**
     * Do not add "()" on Expressions
     * @see https://github.com/rectorphp/rector/pull/401#discussion_r181487199
     */
    protected function pExpr_Yield(Yield_ $yield, int $precedence, int $lhsPrecedence) : string
    {
        if (!$yield->value instanceof Expr) {
            return 'yield';
        }
        // brackets are needed only in case of assign, @see https://www.php.net/manual/en/language.generators.syntax.php
        $shouldAddBrackets = (bool) $yield->getAttribute(AttributeKey::IS_ASSIGNED_TO);
        return \sprintf('%syield %s%s%s', $shouldAddBrackets ? '(' : '', $yield->key instanceof Expr ? $this->p($yield->key) . ' => ' : '', $this->p($yield->value), $shouldAddBrackets ? ')' : '');
    }
    /**
     * Print arrays in short [] by default,
     * to prevent manual explicit array shortening.
     */
    protected function pExpr_Array(Array_ $array) : string
    {
        if (!$array->hasAttribute(AttributeKey::KIND)) {
            $array->setAttribute(AttributeKey::KIND, Array_::KIND_SHORT);
        }
        if ($array->getAttribute(AttributeKey::NEWLINED_ARRAY_PRINT) === \true) {
            $printedArray = '[';
            $printedArray .= $this->pCommaSeparatedMultiline($array->items, \true);
            return $printedArray . ($this->nl . ']');
        }
        return parent::pExpr_Array($array);
    }
    /**
     * Fixes escaping of regular patterns
     */
    protected function pScalar_String(String_ $string) : string
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
            return $this->wrapValueWith($string, '"');
        }
        if ($kind === String_::KIND_SINGLE_QUOTED) {
            return $this->wrapValueWith($string, "'");
        }
        return parent::pScalar_String($string);
    }
    /**
     * It remove all spaces extra to parent
     */
    protected function pStmt_Declare(Declare_ $declare) : string
    {
        $declareString = parent::pStmt_Declare($declare);
        return Strings::replace($declareString, '#\\s+#');
    }
    protected function pExpr_Ternary(Ternary $ternary, int $precedence, int $lhsPrecedence) : string
    {
        $kind = $ternary->getAttribute(AttributeKey::KIND);
        if ($kind === 'wrapped_with_brackets') {
            $pExprTernary = parent::pExpr_Ternary($ternary, $precedence, $lhsPrecedence);
            return '(' . $pExprTernary . ')';
        }
        return parent::pExpr_Ternary($ternary, $precedence, $lhsPrecedence);
    }
    protected function pScalar_InterpolatedString(InterpolatedString $interpolatedString) : string
    {
        $content = parent::pScalar_InterpolatedString($interpolatedString);
        if ($interpolatedString->getAttribute(AttributeKey::DOC_INDENTATION) === '__REMOVED__') {
            return $this->cleanStartIndentationOnHeredocNowDoc($content);
        }
        return $content;
    }
    /**
     * Invoke re-print even if only raw value was changed.
     * That allows PHPStan to use int strict types, while changing the value with literal "_"
     */
    protected function pScalar_Int(Int_ $int) : string
    {
        if ($this->shouldPrintNewRawValue($int)) {
            return (string) $int->getAttribute(AttributeKey::RAW_VALUE);
        }
        return parent::pScalar_Int($int);
    }
    protected function pExpr_MethodCall(MethodCall $methodCall) : string
    {
        if (SimpleParameterProvider::provideBoolParameter(Option::NEW_LINE_ON_FLUENT_CALL) === \false) {
            return parent::pExpr_MethodCall($methodCall);
        }
        if ($methodCall->var instanceof CallLike) {
            foreach ($methodCall->args as $arg) {
                if (!$arg instanceof Arg) {
                    continue;
                }
                $arg->value->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            }
            return $this->pDereferenceLhs($methodCall->var) . "\n" . $this->resolveIndentSpaces() . '->' . $this->pObjectProperty($methodCall->name) . '(' . $this->pMaybeMultiline($methodCall->args) . ')';
        }
        return parent::pExpr_MethodCall($methodCall);
    }
    /**
     * Keep attributes on newlines
     */
    protected function pParam(Param $param) : string
    {
        return $this->pAttrGroups($param->attrGroups) . $this->pModifiers($param->flags) . ($param->type instanceof Node ? $this->p($param->type) . ' ' : '') . ($param->byRef ? '&' : '') . ($param->variadic ? '...' : '') . $this->p($param->var) . ($param->default instanceof Expr ? ' = ' . $this->p($param->default) : '') . ($param->hooks !== [] ? ' {' . $this->pStmts($param->hooks) . $this->nl . '}' : '');
    }
    private function cleanStartIndentationOnHeredocNowDoc(string $content) : string
    {
        $lines = NewLineSplitter::split($content);
        $trimmedLines = \array_map('ltrim', $lines);
        return \implode("\n", $trimmedLines);
    }
    private function resolveIndentSpaces() : string
    {
        $indentSize = SimpleParameterProvider::provideIntParameter(Option::INDENT_SIZE);
        return \str_repeat($this->getIndentCharacter(), $this->indentLevel) . \str_repeat($this->getIndentCharacter(), $indentSize);
    }
    /**
     * Must be a method to be able to react to changed parameter in tests
     */
    private function getIndentCharacter() : string
    {
        return SimpleParameterProvider::provideStringParameter(Option::INDENT_CHAR, ' ');
    }
    /**
     * @param \PhpParser\Node\Scalar\Int_|\PhpParser\Node\Scalar\Float_ $lNumber
     */
    private function shouldPrintNewRawValue($lNumber) : bool
    {
        return $lNumber->getAttribute(AttributeKey::REPRINT_RAW_VALUE) === \true;
    }
    /**
     * @param Node[] $stmts
     * @return Node[]|mixed[]
     */
    private function resolveNewStmts(array $stmts) : array
    {
        $stmts = \array_values($stmts);
        if (\count($stmts) === 1 && $stmts[0] instanceof FileWithoutNamespace) {
            return \array_values($stmts[0]->stmts);
        }
        return $stmts;
    }
    /**
     * @param Node[] $nodes
     */
    private function containsNop(array $nodes) : bool
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
    private function wrapValueWith(String_ $string, string $wrap) : string
    {
        return $wrap . $string->value . $wrap;
    }
}
