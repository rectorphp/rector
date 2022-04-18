<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Parser;

use RectorPrefix20220418\Nette\Utils\Strings;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\StringUtils;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use RectorPrefix20220418\Symplify\SmartFileSystem\SmartFileSystem;
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
    public function __construct(\Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter, \Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator, \Rector\Core\PhpParser\Parser\SimplePhpParser $simplePhpParser, \RectorPrefix20220418\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
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
        $content = \Rector\Core\Util\StringUtils::isMatch($content, self::OPEN_PHP_TAG_REGEX) ? $content : '<?php ' . $content;
        $content = \Rector\Core\Util\StringUtils::isMatch($content, self::ENDING_SEMI_COLON_REGEX) ? $content : $content . ';';
        $stmts = $this->simplePhpParser->parseString($content);
        return $this->nodeScopeAndMetadataDecorator->decorateStmtsFromString($stmts);
    }
    public function stringify(\PhpParser\Node\Expr $expr) : string
    {
        if ($expr instanceof \PhpParser\Node\Scalar\String_) {
            return $expr->value;
        }
        if ($expr instanceof \PhpParser\Node\Scalar\Encapsed) {
            // remove "
            $expr = \trim($this->nodePrinter->print($expr), '""');
            // use \$ → $
            $expr = \RectorPrefix20220418\Nette\Utils\Strings::replace($expr, self::PRESLASHED_DOLLAR_REGEX, '$');
            // use \'{$...}\' → $...
            return \RectorPrefix20220418\Nette\Utils\Strings::replace($expr, self::CURLY_BRACKET_WRAPPER_REGEX, '$1');
        }
        if ($expr instanceof \PhpParser\Node\Expr\BinaryOp\Concat) {
            return $this->stringify($expr->left) . $this->stringify($expr->right);
        }
        if ($expr instanceof \PhpParser\Node\Expr\Variable || $expr instanceof \PhpParser\Node\Expr\PropertyFetch || $expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
            return $this->nodePrinter->print($expr);
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException(\get_class($expr) . ' ' . __METHOD__);
    }
}
