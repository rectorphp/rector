<?php

declare (strict_types=1);
namespace Rector\Util;

use RectorPrefix202611\Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Console\Style\RectorStyle;
use Rector\CustomRules\SimpleNodeDumper;
final class NodePrinter
{
    /**
     * @readonly
     */
    private RectorStyle $rectorStyle;
    /**
     * @see https://regex101.com/r/Fe8n73/1
     * @var string
     */
    private const CLASS_NAME_REGEX = '#(?<class_name>PhpParser(.*?))\(#ms';
    /**
     * @see https://regex101.com/r/uQFuvL/1
     * @var string
     */
    private const PROPERTY_KEY_REGEX = '#(?<key>[\w\d]+)\:#';
    public function __construct(RectorStyle $rectorStyle)
    {
        $this->rectorStyle = $rectorStyle;
    }
    /**
     * @param Node|Node[] $nodes
     */
    public function printNodes($nodes): void
    {
        $dumpedNodesContents = SimpleNodeDumper::dump($nodes);
        // colorize
        $colorContents = $this->addConsoleColors($dumpedNodesContents);
        $this->rectorStyle->writeln($colorContents);
        $this->rectorStyle->newLine();
    }
    private function addConsoleColors(string $contents): string
    {
        // decorate class names
        $colorContents = Strings::replace($contents, self::CLASS_NAME_REGEX, static fn(array $match): string => '<fg=green>' . $match['class_name'] . '</>(');
        // decorate keys
        return Strings::replace($colorContents, self::PROPERTY_KEY_REGEX, static fn(array $match): string => '<fg=yellow>' . $match['key'] . '</>:');
    }
}
