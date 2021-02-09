<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt;
use PhpParser\Parser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
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
    private function parse(string $content): array
    {
        // wrap code so php-parser can interpret it
        $content = Strings::startsWith($content, '<?php ') ? $content : '<?php ' . $content;
        $content = Strings::endsWith($content, ';') ? $content : $content . ';';

        $nodes = (array) $this->parser->parse($content);

        return $this->nodeScopeAndMetadataDecorator->decorateNodesFromString($nodes);
    }
}
