<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Parser;

use RectorPrefix202208\Nette\Utils\Strings;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Util\StringUtils;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileSystem;
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
     * @var string
     * @see https://regex101.com/r/8fDjnR/1
     */
    private const VARIABLE_IN_SINGLE_QUOTED_REGEX = '#\'(?<variable>\\$.*)\'#U';
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
    public function __construct(NodePrinterInterface $nodePrinter, NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\Core\PhpParser\Parser\SimplePhpParser $simplePhpParser, SmartFileSystem $smartFileSystem)
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
            $string = $this->stringify($expr->left) . $this->stringify($expr->right);
            return Strings::replace($string, self::VARIABLE_IN_SINGLE_QUOTED_REGEX, static function (array $match) {
                return $match['variable'];
            });
        }
        return $this->nodePrinter->print($expr);
    }
}
