<?php

declare(strict_types=1);

namespace Rector\PhpParser\Printer;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\Yield_;
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
use PhpParser\PrettyPrinter\Standard;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Tests\PhpParser\Printer\BetterStandardPrinterTest
 */
final class BetterStandardPrinter extends Standard
{
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

    public function printFormatPreserving(array $stmts, array $origStmts, array $origTokens): string
    {
        // detect per print
        $this->detectTabOrSpaceIndentCharacter($stmts);

        $content = parent::printFormatPreserving($stmts, $origStmts, $origTokens);

        // remove dead <?php structures
        $clearContent = Strings::replace($content, '#\<\?php(\s)\?\>\n?#');
        return Strings::replace($clearContent, '#\<\?php(\s+)\?\>#');
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
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    public function areNodesEqual($firstNode, $secondNode): bool
    {
        return $this->print($firstNode) === $this->print($secondNode);
    }

    /**
     * @param Node|Node[]|null $firstNode
     * @param Node|Node[]|null $secondNode
     */
    public function areNodesWithoutCommentsEqual($firstNode, $secondNode): bool
    {
        return $this->printWithoutComments($firstNode) === $this->printWithoutComments($secondNode);
    }

    /**
     * This allows to use both spaces and tabs vs. original space-only
     */
    protected function setIndentLevel(int $level): void
    {
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

        // remove extra spaces before new Nop_ nodes, @see https://regex101.com/r/iSvroO/1
        return Strings::replace($content, '#^[ \t]+$#m');
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
        return "'" . Strings::replace($string, "#'|\\\\(?=[\\\\']|$)#", '\\\\$0') . "'";
    }

    /**
     * Emulates 1_000 in PHP 7.3- version
     */
    protected function pScalar_DNumber(DNumber $DNumber): string
    {
        if (is_string($DNumber->value)) {
            return $DNumber->value;
        }

        return parent::pScalar_DNumber($DNumber);
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

        return Strings::replace($closureContent, '#( use)\(#', '$1 (');
    }

    /**
     * Do not add "()" on Expressions
     * @see https://github.com/rectorphp/rector/pull/401#discussion_r181487199
     */
    protected function pExpr_Yield(Yield_ $node): string
    {
        if ($node->value === null) {
            return 'yield';
        }

        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        $shouldAddBrackets = $parentNode instanceof Expression;

        return sprintf(
            '%syield %s%s%s',
            $shouldAddBrackets ? '(' : '',
            $node->key !== null ? $this->p($node->key) . ' => ' : '',
            $this->p($node->value),
            $shouldAddBrackets ? ')' : ''
        );
    }

    /**
     * Print arrays in short [] by default,
     * to prevent manual explicit array shortening.
     */
    protected function pExpr_Array(Array_ $node): string
    {
        if (! $node->hasAttribute(AttributeKey::KIND)) {
            $node->setAttribute(AttributeKey::KIND, Array_::KIND_SHORT);
        }

        return parent::pExpr_Array($node);
    }

    /**
     * Fixes escaping of regular patterns
     */
    protected function pScalar_String(String_ $node): string
    {
        $kind = $node->getAttribute(AttributeKey::KIND, String_::KIND_SINGLE_QUOTED);
        if ($kind === String_::KIND_DOUBLE_QUOTED && $node->getAttribute('is_regular_pattern')) {
            return '"' . $node->value . '"';
        }

        return parent::pScalar_String($node);
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
        return $this->pModifiers($classMethod->flags)
            . 'function ' . ($classMethod->byRef ? '&' : '') . $classMethod->name
            . '(' . $this->pCommaSeparated($classMethod->params) . ')'
            . ($classMethod->returnType !== null ? ': ' . $this->p($classMethod->returnType) : '')
            . ($classMethod->stmts !== null ? $this->nl . '{' . $this->pStmts(
                $classMethod->stmts
            ) . $this->nl . '}' : ';');
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

            $whitespacesChars = Strings::matchAll($fileInfo->getContents(), '#^( |\t)#m');
            foreach ($whitespacesChars as $whitespacesChar) {
                // let the first win
                $this->tabOrSpaceIndentCharacter = $whitespacesChar[0];
                break;
            }
        }
    }

    private function removeComments(string $printerNode): string
    {
        $printerNode = Strings::replace($printerNode, '#\/*\*(.*?)\*\/#');
        return Strings::replace($printerNode, '#\/\/(.*?)$#m');
    }

    /**
     * @todo decopule
     */
    private function moveCommentsFromAttributeObjectToCommentsAttribute(array $nodes): void
    {
        // move phpdoc from node to "comment" attirbute
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
}
