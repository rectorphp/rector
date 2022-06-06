<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\PhpParser\Parser;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Concat;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticPropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\Encapsed;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt;
use RectorPrefix20220606\PhpParser\Parser;
use RectorPrefix20220606\Rector\Core\Contract\PhpParser\NodePrinterInterface;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Util\StringUtils;
use RectorPrefix20220606\Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use RectorPrefix20220606\Symplify\SmartFileSystem\SmartFileSystem;
final class InlineCodeParser
{
    /**
     * @var string
     * @see https://regex101.com/r/dwe4OW/1
     */
    private const PRESLASHED_DOLLAR_REGEX = '#\\\\\\$#';
    /**
     * @var string
     * @see https://regex101.com/r/tvwhWq/1
     */
    private const CURLY_BRACKET_WRAPPER_REGEX = "#'{(\\\$.*?)}'#";
    /**
     * @var string
     * @see https://regex101.com/r/TBlhoR/1
     */
    private const OPEN_PHP_TAG_REGEX = '#^\\<\\?php\\s+#';
    /**
     * @var string
     * @see https://regex101.com/r/TUWwKw/1/
     */
    private const ENDING_SEMI_COLON_REGEX = '#;(\\s+)?$#';
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    /**
     * @readonly
     * @var \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(NodePrinterInterface $nodePrinter, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, SimplePhpParser $simplePhpParser, SmartFileSystem $smartFileSystem)
    {
        $this->nodePrinter = $nodePrinter;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
        $this->simplePhpParser = $simplePhpParser;
        $this->smartFileSystem = $smartFileSystem;
    }
    /**
     * @return Stmt[]
     */
    public function parse(string $content) : array
    {
        // to cover files too
        if (\is_file($content)) {
            $content = $this->smartFileSystem->readFile($content);
        }
        // wrap code so php-parser can interpret it
        $content = StringUtils::isMatch($content, self::OPEN_PHP_TAG_REGEX) ? $content : '<?php ' . $content;
        $content = StringUtils::isMatch($content, self::ENDING_SEMI_COLON_REGEX) ? $content : $content . ';';
        $stmts = $this->simplePhpParser->parseString($content);
        return $this->nodeScopeAndMetadataDecorator->decorateStmtsFromString($stmts);
    }
    public function stringify(Expr $expr) : string
    {
        if ($expr instanceof String_) {
            return $expr->value;
        }
        if ($expr instanceof Encapsed) {
            // remove "
            $expr = \trim($this->nodePrinter->print($expr), '""');
            // use \$ → $
            $expr = Strings::replace($expr, self::PRESLASHED_DOLLAR_REGEX, '$');
            // use \'{$...}\' → $...
            return Strings::replace($expr, self::CURLY_BRACKET_WRAPPER_REGEX, '$1');
        }
        if ($expr instanceof Concat) {
            return $this->stringify($expr->left) . $this->stringify($expr->right);
        }
        if ($expr instanceof Variable || $expr instanceof PropertyFetch || $expr instanceof StaticPropertyFetch) {
            return $this->nodePrinter->print($expr);
        }
        throw new ShouldNotHappenException(\get_class($expr) . ' ' . __METHOD__);
    }
}
