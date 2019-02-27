<?php declare(strict_types=1);

namespace Rector\PhpParser\Parser;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\Encapsed;
use PhpParser\Node\Scalar\String_;
use PhpParser\Parser;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\NodeScopeAndMetadataDecorator;
use Rector\PhpParser\Printer\BetterStandardPrinter;

final class InlineCodeParser
{
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
        Parser $parser,
        BetterStandardPrinter $betterStandardPrinter,
        NodeScopeAndMetadataDecorator $nodeScopeAndMetadataDecorator
    ) {
        $this->parser = $parser;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeScopeAndMetadataDecorator = $nodeScopeAndMetadataDecorator;
    }

    /**
     * @return Node[]
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
            $content = Strings::replace($content, '#\\\\\$#', '$');
            // use \'{$...}\' → $...
            return Strings::replace($content, "#'{(\\\$.*?)}'#", '$1');
        }

        if ($content instanceof Concat) {
            return $this->stringify($content->left) . $this->stringify($content->right);
        }

        if ($content instanceof Variable || $content instanceof PropertyFetch) {
            return $this->betterStandardPrinter->print($content);
        }

        throw new ShouldNotHappenException(get_class($content) . ' ' . __METHOD__);
    }
}
