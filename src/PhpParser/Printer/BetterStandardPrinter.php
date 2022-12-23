<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Printer;

use RectorPrefix202212\Nette\Utils\Strings;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Use_;
use PhpParser\PrettyPrinter\Standard;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Core\Configuration\RectorConfigProvider;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Util\StringUtils;
use Rector\NodeTypeResolver\Node\AttributeKey;
/**
 * @see \Rector\Core\Tests\PhpParser\Printer\BetterStandardPrinterTest
 *
 * @property array<string, array{string, bool, string, null}> $insertionMap
 */
final class BetterStandardPrinter extends Standard implements NodePrinterInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/jUFizd/1
     */
    private const NEWLINE_END_REGEX = "#\n\$#";
    /**
     * @var string
     * @see https://regex101.com/r/F5x783/1
     */
    private const USE_REGEX = '#( use)\\(#';
    /**
     * @var string
     * @see https://regex101.com/r/DrsMY4/1
     */
    private const QUOTED_SLASH_REGEX = "#'|\\\\(?=[\\\\']|\$)#";
    /**
     * Remove extra spaces before new Nop_ nodes
     * @see https://regex101.com/r/iSvroO/1
     * @var string
     */
    private const EXTRA_SPACE_BEFORE_NOP_REGEX = '#^[ \\t]+$#m';
    /**
     * @see https://regex101.com/r/qZiqGo/13
     * @var string
     */
    private const REPLACE_COLON_WITH_SPACE_REGEX = '#(^.*function .*\\(.*\\)) : #';
    /**
     * Use space by default
     * @var string
     */
    private $tabOrSpaceIndentCharacter = ' ';
    /**
     * @readonly
     * @var \Rector\Comments\NodeDocBlock\DocBlockUpdater
     */
    private $docBlockUpdater;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\RectorConfigProvider
     */
    private $rectorConfigProvider;
    /**
     * @param mixed[] $options
     */
    public function __construct(DocBlockUpdater $docBlockUpdater, RectorConfigProvider $rectorConfigProvider, array $options = [])
    {
        $this->docBlockUpdater = $docBlockUpdater;
        $this->rectorConfigProvider = $rectorConfigProvider;
        parent::__construct($options);
        // print return type double colon right after the bracket "function(): string"
        $this->initializeInsertionMap();
        $this->insertionMap['Stmt_ClassMethod->returnType'] = [')', \false, ': ', null];
        $this->insertionMap['Stmt_Function->returnType'] = [')', \false, ': ', null];
        $this->insertionMap['Expr_Closure->returnType'] = [')', \false, ': ', null];
        $this->insertionMap['Expr_ArrowFunction->returnType'] = [')', \false, ': ', null];
        $this->tabOrSpaceIndentCharacter = $this->rectorConfigProvider->getIndentChar();
    }
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
        if (\count($stmts) !== \count($origStmts) && !StringUtils::isMatch($content, self::NEWLINE_END_REGEX)) {
            $content .= $this->nl;
        }
        return $content;
    }
    /**
     * @param \PhpParser\Node|mixed[]|null $node
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
        $content = $this->pStmts($fileWithoutNamespace->stmts, \false);
        return \ltrim($content);
    }
    protected function p(Node $node, $parentFormatPreserved = \false) : string
    {
        $content = parent::p($node, $parentFormatPreserved);
        if ($node instanceof Expr) {
            $content = $this->resolveContentOnExpr($node, $content);
        }
        return $node->getAttribute(AttributeKey::WRAPPED_IN_PARENTHESES) === \true ? '(' . $content . ')' : $content;
    }
    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function setIndentLevel(int $level) : void
    {
        $level = \max($level, 0);
        $this->indentLevel = $level;
        $this->nl = "\n" . \str_repeat($this->tabOrSpaceIndentCharacter, $level);
    }
    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function indent() : void
    {
        $indentSize = $this->rectorConfigProvider->getIndentSize();
        $this->indentLevel += $indentSize;
        $this->nl .= \str_repeat($this->tabOrSpaceIndentCharacter, $indentSize);
    }
    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function outdent() : void
    {
        if ($this->tabOrSpaceIndentCharacter === ' ') {
            // - 4 spaces
            \assert($this->indentLevel >= 4);
            $this->indentLevel -= 4;
        } else {
            // - 1 tab
            \assert($this->indentLevel >= 1);
            --$this->indentLevel;
        }
        $this->nl = "\n" . \str_repeat($this->tabOrSpaceIndentCharacter, $this->indentLevel);
    }
    /**
     * @param mixed[] $nodes
     * @param mixed[] $origNodes
     * @param int|null $fixup
     */
    protected function pArray(array $nodes, array $origNodes, int &$pos, int $indentAdjustment, string $parentNodeType, string $subNodeName, $fixup) : ?string
    {
        // reindex positions for printer
        $nodes = \array_values($nodes);
        $this->moveCommentsFromAttributeObjectToCommentsAttribute($nodes);
        $content = parent::pArray($nodes, $origNodes, $pos, $indentAdjustment, $parentNodeType, $subNodeName, $fixup);
        if ($content === null) {
            return $content;
        }
        if (!$this->containsNop($nodes)) {
            return $content;
        }
        return Strings::replace($content, self::EXTRA_SPACE_BEFORE_NOP_REGEX, '');
    }
    /**
     * Do not preslash all slashes (parent behavior), but only those:
     *
     * - followed by "\"
     * - by "'"
     * - or the end of the string
     *
     * Prevents `Vendor\Class` => `Vendor\\Class`.
     */
    protected function pSingleQuotedString(string $string) : string
    {
        return "'" . Strings::replace($string, self::QUOTED_SLASH_REGEX, '\\\\$0') . "'";
    }
    /**
     * Emulates 1_000 in PHP 7.3- version
     */
    protected function pScalar_DNumber(DNumber $dNumber) : string
    {
        if ($this->shouldPrintNewRawValue($dNumber)) {
            return (string) $dNumber->getAttribute(AttributeKey::RAW_VALUE);
        }
        return parent::pScalar_DNumber($dNumber);
    }
    /**
     * Add space:
     * "use("
     * ↓
     * "use ("
     */
    protected function pExpr_Closure(Closure $closure) : string
    {
        $closureContent = parent::pExpr_Closure($closure);
        if ($closure->uses === []) {
            return $closureContent;
        }
        return Strings::replace($closureContent, self::USE_REGEX, '$1 (');
    }
    /**
     * Do not add "()" on Expressions
     * @see https://github.com/rectorphp/rector/pull/401#discussion_r181487199
     */
    protected function pExpr_Yield(Yield_ $yield) : string
    {
        if ($yield->value === null) {
            return 'yield';
        }
        $parentNode = $yield->getAttribute(AttributeKey::PARENT_NODE);
        // brackets are needed only in case of assign, @see https://www.php.net/manual/en/language.generators.syntax.php
        $shouldAddBrackets = $parentNode instanceof Assign;
        return \sprintf('%syield %s%s%s', $shouldAddBrackets ? '(' : '', $yield->key !== null ? $this->p($yield->key) . ' => ' : '', $this->p($yield->value), $shouldAddBrackets ? ')' : '');
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
        return parent::pExpr_Array($array);
    }
    /**
     * Fixes escaping of regular patterns
     */
    protected function pScalar_String(String_ $string) : string
    {
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
     * @param Node[] $nodes
     */
    protected function pStmts(array $nodes, bool $indent = \true) : string
    {
        $this->moveCommentsFromAttributeObjectToCommentsAttribute($nodes);
        return parent::pStmts($nodes, $indent);
    }
    /**
     * "...$params) : ReturnType"
     * ↓
     * "...$params): ReturnType"
     */
    protected function pStmt_ClassMethod(ClassMethod $classMethod) : string
    {
        $content = parent::pStmt_ClassMethod($classMethod);
        if (!$classMethod->returnType instanceof Node) {
            return $content;
        }
        // this approach is chosen, to keep changes in parent pStmt_ClassMethod() updated
        return Strings::replace($content, self::REPLACE_COLON_WITH_SPACE_REGEX, '$1: ');
    }
    /**
     * It remove all spaces extra to parent
     */
    protected function pStmt_Declare(Declare_ $declare) : string
    {
        $declareString = parent::pStmt_Declare($declare);
        return Strings::replace($declareString, '#\\s+#', '');
    }
    protected function pExpr_Ternary(Ternary $ternary) : string
    {
        $kind = $ternary->getAttribute(AttributeKey::KIND);
        if ($kind === 'wrapped_with_brackets') {
            $pExprTernary = parent::pExpr_Ternary($ternary);
            return '(' . $pExprTernary . ')';
        }
        return parent::pExpr_Ternary($ternary);
    }
    /**
     * Remove extra \\ from FQN use imports, for easier use in the code
     */
    protected function pStmt_Use(Use_ $use) : string
    {
        if ($use->type !== Use_::TYPE_NORMAL) {
            return parent::pStmt_Use($use);
        }
        foreach ($use->uses as $useUse) {
            if (!$useUse->name instanceof FullyQualified) {
                continue;
            }
            $useUse->name = new Name($useUse->name->toString());
        }
        return parent::pStmt_Use($use);
    }
    protected function pScalar_EncapsedStringPart(EncapsedStringPart $encapsedStringPart) : string
    {
        // parent throws exception, but we need to compare string
        return '`' . $encapsedStringPart->value . '`';
    }
    protected function pCommaSeparated(array $nodes) : string
    {
        $result = parent::pCommaSeparated($nodes);
        $last = \end($nodes);
        if ($last instanceof Node) {
            $trailingComma = $last->getAttribute(AttributeKey::FUNC_ARGS_TRAILING_COMMA);
            if ($trailingComma === \false) {
                $result = \rtrim($result, ',');
            }
        }
        return $result;
    }
    /**
     * Override parent pModifiers to set position of final and abstract modifier early, so instead of
     *
     *      public final const MY_CONSTANT = "Hello world!";
     *
     * it should be
     *
     *      final public const MY_CONSTANT = "Hello world!";
     *
     * @see https://github.com/rectorphp/rector/issues/6963
     * @see https://github.com/nikic/PHP-Parser/pull/826
     */
    protected function pModifiers(int $modifiers) : string
    {
        return (($modifiers & Class_::MODIFIER_FINAL) !== 0 ? 'final ' : '') . (($modifiers & Class_::MODIFIER_ABSTRACT) !== 0 ? 'abstract ' : '') . (($modifiers & Class_::MODIFIER_PUBLIC) !== 0 ? 'public ' : '') . (($modifiers & Class_::MODIFIER_PROTECTED) !== 0 ? 'protected ' : '') . (($modifiers & Class_::MODIFIER_PRIVATE) !== 0 ? 'private ' : '') . (($modifiers & Class_::MODIFIER_STATIC) !== 0 ? 'static ' : '') . (($modifiers & Class_::MODIFIER_READONLY) !== 0 ? 'readonly ' : '');
    }
    /**
     * Invoke re-print even if only raw value was changed.
     * That allows PHPStan to use int strict types, while changing the value with literal "_"
     * @return string|int
     */
    protected function pScalar_LNumber(LNumber $lNumber)
    {
        if ($this->shouldPrintNewRawValue($lNumber)) {
            return (string) $lNumber->getAttribute(AttributeKey::RAW_VALUE);
        }
        return parent::pScalar_LNumber($lNumber);
    }
    /**
     * Keep attributes on newlines
     */
    protected function pParam(Param $param) : string
    {
        return $this->pAttrGroups($param->attrGroups) . $this->pModifiers($param->flags) . ($param->type instanceof Node ? $this->p($param->type) . ' ' : '') . ($param->byRef ? '&' : '') . ($param->variadic ? '...' : '') . $this->p($param->var) . ($param->default instanceof Expr ? ' = ' . $this->p($param->default) : '');
    }
    /**
     * @param \PhpParser\Node\Scalar\LNumber|\PhpParser\Node\Scalar\DNumber $lNumber
     */
    private function shouldPrintNewRawValue($lNumber) : bool
    {
        return $lNumber->getAttribute(AttributeKey::REPRINT_RAW_VALUE) === \true;
    }
    private function resolveContentOnExpr(Expr $expr, string $content) : string
    {
        $parentNode = $expr->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof ArrowFunction) {
            return $content;
        }
        if ($parentNode->expr !== $expr) {
            return $content;
        }
        if (!$parentNode->hasAttribute(AttributeKey::COMMENT_CLOSURE_RETURN_MIRRORED)) {
            return $content;
        }
        /** @var Comment[] $comments */
        $comments = $expr->getAttribute(AttributeKey::COMMENTS) ?? [];
        if ($comments === []) {
            return $content;
        }
        $text = '';
        foreach ($comments as $comment) {
            $text .= $comment->getText() . "\n";
        }
        return $content = $text . $content;
    }
    /**
     * @param Node[] $stmts
     * @return Node[]|mixed[]
     */
    private function resolveNewStmts(array $stmts) : array
    {
        if (\count($stmts) === 1 && $stmts[0] instanceof FileWithoutNamespace) {
            return $this->resolveNewStmts($stmts[0]->stmts);
        }
        return $stmts;
    }
    /**
     * @param array<Node|null> $nodes
     */
    private function moveCommentsFromAttributeObjectToCommentsAttribute(array $nodes) : void
    {
        // move phpdoc from node to "comment" attribute
        foreach ($nodes as $node) {
            if (!$node instanceof Node) {
                continue;
            }
            $this->docBlockUpdater->updateNodeWithPhpDocInfo($node);
        }
    }
    /**
     * @param Node[] $nodes
     */
    private function containsNop(array $nodes) : bool
    {
        foreach ($nodes as $node) {
            if ($node instanceof Nop) {
                return \true;
            }
        }
        return \false;
    }
    private function wrapValueWith(String_ $string, string $wrap) : string
    {
        return $wrap . $string->value . $wrap;
    }
}
