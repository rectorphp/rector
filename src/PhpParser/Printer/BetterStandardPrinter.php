<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Printer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\EncapsedStringPart;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\TraitUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\PrettyPrinter\Standard;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Core\Tests\PhpParser\Printer\BetterStandardPrinterTest
 */
final class BetterStandardPrinter extends Standard
{
    /**
     * @var string
     */
    private const START_COMMENT_REGEX = '#\/*\*(.*?)\*\/#s';

    /**
     * @var string
     */
    private const START_GRID_COMMENT_REGEX = '#^(\s+)?\#(.*?)$#m';

    /**
     * @var string
     */
    private const START_DOUBLE_SLASH_COMMENT_REGEX = '#\/\/(.*?)$#m';

    /**
     * @var string
     */
    private const NEWLINE_END_REGEX = "#\n$#";

    /**
     * @var string
     */
    private const FOUR_SPACE_START_REGEX = '#^ {4}#m';

    /**
     * @var string
     */
    private const USE_REGEX = '#( use)\(#';

    /**
     * @var string
     */
    private const QUOTED_SLASH_REGEX = "#'|\\\\(?=[\\\\']|$)#";

    /**
     * Remove extra spaces before new Nop_ nodes
     * @see https://regex101.com/r/iSvroO/1
     * @var string
     */
    private const EXTRA_SPACE_BEFORE_NOP_REGEX = '#^[ \t]+$#m';

    /**
     * @see https://regex101.com/r/qZiqGo/4
     * @var string
     */
    private const REPLACE_COLON_WITH_SPACE_REGEX = '#(function .*?\(.*?\)) : #';

    /**
     * Use space by default
     * @var string
     */
    private $tabOrSpaceIndentCharacter = ' ';

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    /**
     * @param mixed[] $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);

        // print return type double colon right after the bracket "function(): string"
        $this->initializeInsertionMap();
        $this->insertionMap['Stmt_ClassMethod->returnType'] = [')', false, ': ', null];
        $this->insertionMap['Stmt_Function->returnType'] = [')', false, ': ', null];
        $this->insertionMap['Expr_Closure->returnType'] = [')', false, ': ', null];
    }

    /**
     * @required
     */
    public function autowireBetterStandardPrinter(DocBlockManipulator $docBlockManipulator): void
    {
        $this->docBlockManipulator = $docBlockManipulator;
    }

    /**
     * @param Node[] $stmts
     * @param Node[] $origStmts
     * @param mixed[] $origTokens
     */
    public function printFormatPreserving(array $stmts, array $origStmts, array $origTokens): string
    {
        $newStmts = $this->resolveNewStmts($stmts);

        // detect per print
        $this->detectTabOrSpaceIndentCharacter($newStmts);

        $content = parent::printFormatPreserving($newStmts, $origStmts, $origTokens);

        // add new line in case of added stmts
        if (count($stmts) !== count($origStmts) && ! (bool) Strings::match($content, self::NEWLINE_END_REGEX)) {
            $content .= $this->nl;
        }

        return $content;
    }

    /**
     * @param Node|Node[]|null $node
     */
    public function printWithoutComments($node): string
    {
        $printerNode = $this->print($node);

        $nodeWithoutComments = $this->removeComments($printerNode);
        return trim($nodeWithoutComments);
    }

    /**
     * @param Node|Node[]|null $node
     */
    public function print($node): string
    {
        if ($node === null) {
            $node = [];
        }

        if ($node instanceof EncapsedStringPart) {
            return 'UNABLE_TO_PRINT_ENCAPSED_STRING';
        }

        if (! is_array($node)) {
            $node = [$node];
        }

        return $this->prettyPrint($node);
    }

    /**
     * Removes all comments from both nodes
     *
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    public function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->printWithoutComments($firstNode) === $this->printWithoutComments($secondNode);
    }

    /**
     * @param Node[] $stmts Array of statements
     */
    public function prettyPrintFile(array $stmts): string
    {
        // to keep indexes from 0
        if (is_array($stmts)) {
            $stmts = array_values($stmts);
        }

        if ($stmts === null) {
            $stmts = [];
        } elseif (! is_array($stmts)) {
            $stmts = [$stmts];
        }

        return parent::prettyPrintFile($stmts) . PHP_EOL;
    }

    public function pFileWithoutNamespace(FileWithoutNamespace $fileWithoutNamespace): string
    {
        $content = self::pStmts((array) $fileWithoutNamespace->stmts, false);

        return ltrim($content);
    }

    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function setIndentLevel(int $level): void
    {
        $level = max($level, 0);
        $this->indentLevel = $level;
        $this->nl = "\n" . str_repeat($this->tabOrSpaceIndentCharacter, $level);
    }

    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function indent(): void
    {
        $multiplier = $this->tabOrSpaceIndentCharacter === ' ' ? 4 : 1;

        $this->indentLevel += $multiplier;
        $this->nl .= str_repeat($this->tabOrSpaceIndentCharacter, $multiplier);
    }

    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function outdent(): void
    {
        if ($this->tabOrSpaceIndentCharacter === ' ') {
            // - 4 spaces
            assert($this->indentLevel >= 4);
            $this->indentLevel -= 4;
        } else {
            // - 1 tab
            assert($this->indentLevel >= 1);
            --$this->indentLevel;
        }

        $this->nl = "\n" . str_repeat($this->tabOrSpaceIndentCharacter, $this->indentLevel);
    }

    /**
     * @param mixed[] $nodes
     * @param mixed[] $origNodes
     * @param int|null $fixup
     */
    protected function pArray(
        array $nodes,
        array $origNodes,
        int &$pos,
        int $indentAdjustment,
        string $parentNodeType,
        string $subNodeName,
        $fixup
    ): ?string {
        // reindex positions for printer
        $nodes = array_values($nodes);

        $this->moveCommentsFromAttributeObjectToCommentsAttribute($nodes);

        $content = parent::pArray($nodes, $origNodes, $pos, $indentAdjustment, $parentNodeType, $subNodeName, $fixup);

        if ($content === null) {
            return $content;
        }

        if (! $this->containsNop($nodes)) {
            return $content;
        }

        return Strings::replace($content, self::EXTRA_SPACE_BEFORE_NOP_REGEX);
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
    protected function pSingleQuotedString(string $string): string
    {
        return "'" . Strings::replace($string, self::QUOTED_SLASH_REGEX, '\\\\$0') . "'";
    }

    /**
     * Emulates 1_000 in PHP 7.3- version
     */
    protected function pScalar_DNumber(DNumber $dNumber): string
    {
        if (is_string($dNumber->value)) {
            return $dNumber->value;
        }

        return parent::pScalar_DNumber($dNumber);
    }

    /**
     * Add space:
     * "use("
     * ↓
     * "use ("
     */
    protected function pExpr_Closure(Closure $closure): string
    {
        $closureContent = parent::pExpr_Closure($closure);

        return Strings::replace($closureContent, self::USE_REGEX, '$1 (');
    }

    /**
     * Do not add "()" on Expressions
     * @see https://github.com/rectorphp/rector/pull/401#discussion_r181487199
     */
    protected function pExpr_Yield(Yield_ $yield): string
    {
        if ($yield->value === null) {
            return 'yield';
        }

        $parentNode = $yield->getAttribute(AttributeKey::PARENT_NODE);
        $shouldAddBrackets = $parentNode instanceof Expression;

        return sprintf(
            '%syield %s%s%s',
            $shouldAddBrackets ? '(' : '',
            $yield->key !== null ? $this->p($yield->key) . ' => ' : '',
            $this->p($yield->value),
            $shouldAddBrackets ? ')' : ''
        );
    }

    /**
     * Print arrays in short [] by default,
     * to prevent manual explicit array shortening.
     */
    protected function pExpr_Array(Array_ $array): string
    {
        if (! $array->hasAttribute(AttributeKey::KIND)) {
            $array->setAttribute(AttributeKey::KIND, Array_::KIND_SHORT);
        }

        return parent::pExpr_Array($array);
    }

    /**
     * Fixes escaping of regular patterns
     */
    protected function pScalar_String(String_ $string): string
    {
        if ($string->getAttribute(AttributeKey::IS_REGULAR_PATTERN)) {
            $kind = $string->getAttribute(AttributeKey::KIND, String_::KIND_SINGLE_QUOTED);
            if ($kind === String_::KIND_DOUBLE_QUOTED) {
                return $this->wrapValueWith($string, '"');
            }

            if ($kind === String_::KIND_SINGLE_QUOTED) {
                return $this->wrapValueWith($string, "'");
            }
        }

        return parent::pScalar_String($string);
    }

    /**
     * @param Node[] $nodes
     */
    protected function pStmts(array $nodes, bool $indent = true): string
    {
        $this->moveCommentsFromAttributeObjectToCommentsAttribute($nodes);

        return parent::pStmts($nodes, $indent);
    }

    /**
     * "...$params) : ReturnType"
     * ↓
     * "...$params): ReturnType"
     */
    protected function pStmt_ClassMethod(ClassMethod $classMethod): string
    {
        $content = parent::pStmt_ClassMethod($classMethod);

        // this approach is chosen, to keep changes in parent pStmt_ClassMethod() updated
        return Strings::replace($content, self::REPLACE_COLON_WITH_SPACE_REGEX, '$1: ');
    }

    /**
     * Clean class and trait from empty "use x;" for traits causing invalid code
     */
    protected function pStmt_Class(Class_ $class): string
    {
        $shouldReindex = false;

        foreach ($class->stmts as $key => $stmt) {
            // remove empty ones
            if ($stmt instanceof TraitUse && count($stmt->traits) === 0) {
                unset($class->stmts[$key]);
                $shouldReindex = true;
            }
        }

        if ($shouldReindex) {
            $class->stmts = array_values($class->stmts);
        }

        return parent::pStmt_Class($class);
    }

    /**
     * It remove all spaces extra to parent
     */
    protected function pStmt_Declare(Declare_ $declare): string
    {
        $declareString = parent::pStmt_Declare($declare);

        return Strings::replace($declareString, '#\s+#');
    }

    /**
     * Remove extra \\ from FQN use imports, for easier use in the code
     */
    protected function pStmt_Use(Use_ $use): string
    {
        if ($use->type === Use_::TYPE_NORMAL) {
            foreach ($use->uses as $useUse) {
                if ($useUse->name instanceof FullyQualified) {
                    $useUse->name = new Name($useUse->name);
                }
            }
        }

        return parent::pStmt_Use($use);
    }

    /**
     * @param Node[] $stmts
     * @return Node[]|mixed[]
     */
    private function resolveNewStmts(array $stmts): array
    {
        if (count($stmts) === 1) {
            $onlyStmt = $stmts[0];
            if ($onlyStmt instanceof FileWithoutNamespace) {
                return $onlyStmt->stmts;
            }
        }

        return $stmts;
    }

    /**
     * Solves https://github.com/rectorphp/rector/issues/1964
     *
     * Some files have spaces, some have tabs. Keep the original indent if possible.
     *
     * @param Stmt[] $stmts
     */
    private function detectTabOrSpaceIndentCharacter(array $stmts): void
    {
        // use space by default
        $this->tabOrSpaceIndentCharacter = ' ';

        foreach ($stmts as $stmt) {
            if (! $stmt instanceof Node) {
                continue;
            }

            /** @var SmartFileInfo|null $fileInfo */
            $fileInfo = $stmt->getAttribute(AttributeKey::FILE_INFO);
            if ($fileInfo === null) {
                continue;
            }

            $whitespaces = count(Strings::matchAll($fileInfo->getContents(), self::FOUR_SPACE_START_REGEX));
            $tabs = count(Strings::matchAll($fileInfo->getContents(), '#^\t#m'));

            // tab vs space
            $this->tabOrSpaceIndentCharacter = ($whitespaces <=> $tabs) >= 0 ? ' ' : "\t";
        }
    }

    private function removeComments(string $printerNode): string
    {
        // remove /** ... */
        $printerNode = Strings::replace($printerNode, self::START_COMMENT_REGEX);

        // remove /* ... */
        $printerNode = Strings::replace($printerNode, self::START_COMMENT_REGEX);

        // remove # ...
        $printerNode = Strings::replace($printerNode, self::START_GRID_COMMENT_REGEX);

        // remove // ...
        return Strings::replace($printerNode, self::START_DOUBLE_SLASH_COMMENT_REGEX);
    }

    /**
     * @param Node[] $nodes
     */
    private function moveCommentsFromAttributeObjectToCommentsAttribute(array $nodes): void
    {
        // move phpdoc from node to "comment" attribute
        foreach ($nodes as $node) {
            if (! $node instanceof Node) {
                continue;
            }

            $this->docBlockManipulator->updateNodeWithPhpDocInfo($node);
        }
    }

    /**
     * @param Node[] $nodes
     */
    private function containsNop(array $nodes): bool
    {
        foreach ($nodes as $node) {
            if ($node instanceof Nop) {
                return true;
            }
        }

        return false;
    }

    private function wrapValueWith(String_ $string, string $wrap): string
    {
        return $wrap . $string->value . $wrap;
    }
}
