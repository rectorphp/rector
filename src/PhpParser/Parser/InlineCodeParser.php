<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\Util\StaticInstanceOf;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;

final class InlineCodeParser
{
    /**
     * @var string
     * @see https://regex101.com/r/dwe4OW/1
     */
    private const PRESLASHED_DOLLAR_REGEX = '#\\\\\$#';

    /**
     * @var string
     * @see https://regex101.com/r/tvwhWq/1
     */
    private const CURLY_BRACKET_WRAPPER_REGEX = "#'{(\\\$.*?)}'#";

    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var NodeScopeAndMetadataDecorator
     */
    private $nodeScopeAndMetadataDecorator;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator,
        Parser $parser
    ) {
        $this->parser = $parser;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
    }

    /**
     * @return Stmt[]
     */
    public function parse(string $content): array
    {
        // wrap code so php-parser can interpret it
        $content = Strings::startsWith($content, '<?php ') ? $content : '<?php ' . $content;
        $content = Strings::endsWith($content, ';') ? $content : $content . ';';

        $nodes = (array) $this->parser->parse($content);

        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromString($nodes);
    }

    /**
     * @param string|Node $content
     */
    public function stringify($content): string
    {
        if (is_string($content)) {
            return $content;
        }

        if ($content instanceof String_) {
            return $content->value;
        }

        if ($content instanceof Encapsed) {
            // remove "
            $content = trim($this->betterStandardPrinter->print($content), '""');
            // use \$ → $
            $content = Strings::replace($content, self::PRESLASHED_DOLLAR_REGEX, '$');
            // use \'{$...}\' → $...
            return Strings::replace($content, self::CURLY_BRACKET_WRAPPER_REGEX, '$1');
        }

        if ($content instanceof Concat) {
            return $this->stringify($content->left) . $this->stringify($content->right);
        }

        if (StaticInstanceOf::isOneOf($content, [Variable::class, PropertyFetch::class, StaticPropertyFetch::class])) {
            return $this->betterStandardPrinter->print($content);
        }

        throw new ShouldNotHappenException(get_class($content) . ' ' . __METHOD__);
    }
}
