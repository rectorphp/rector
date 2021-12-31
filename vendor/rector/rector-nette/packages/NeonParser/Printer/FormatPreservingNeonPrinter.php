<?php

declare (strict_types=1);
namespace Rector\Nette\NeonParser\Printer;

use RectorPrefix20211231\Nette\Neon\Node;
use RectorPrefix20211231\Nette\Utils\Strings;
final class FormatPreservingNeonPrinter
{
    /**
     * @var string
     */
    private const INDENT_TABS = 'tabs';
    /**
     * @var string
     */
    private const INDENT_SPACES = 'spaces';
    public function printNode(\RectorPrefix20211231\Nette\Neon\Node $node, string $originalContenet) : string
    {
        $neonContent = $node->toString();
        $indentType = $this->resolveIndentType($originalContenet);
        $neonContent = $this->formatIndent($neonContent, $indentType);
        // replace quotes - @todo resolve defaults
        return \RectorPrefix20211231\Nette\Utils\Strings::replace($neonContent, '#\\"#', '\'');
    }
    /**
     * Some files prefer tabs, some spaces. This will resolve first found space.
     */
    private function resolveIndentType(string $neonContent) : string
    {
        $indentMatch = \RectorPrefix20211231\Nette\Utils\Strings::match($neonContent, '#(\\t|  )#ms');
        if ($indentMatch[0] === "\t") {
            return self::INDENT_TABS;
        }
        return self::INDENT_SPACES;
    }
    private function formatIndent(string $neonContent, string $indentType) : string
    {
        if ($indentType === self::INDENT_SPACES) {
            return \RectorPrefix20211231\Nette\Utils\Strings::replace($neonContent, '#\\t#', '    ');
        }
        return $neonContent;
    }
}
