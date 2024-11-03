<?php

declare (strict_types=1);
namespace Rector\Util;

use RectorPrefix202411\Nette\Utils\Strings;
use PhpParser\Node;
use Rector\CustomRules\SimpleNodeDumper;
use RectorPrefix202411\Symfony\Component\Console\Style\SymfonyStyle;
final class NodePrinter
{
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @var string
     * @see https://regex101.com/r/Fe8n73/1
     */
    private const CLASS_NAME_REGEX = '#(?<class_name>PhpParser(.*?))\\(#ms';
    /**
     * @var string
     * @see https://regex101.com/r/uQFuvL/1
     */
    private const PROPERTY_KEY_REGEX = '#(?<key>[\\w\\d]+)\\:#';
    public function __construct(SymfonyStyle $symfonyStyle)
    {
        $this->symfonyStyle = $symfonyStyle;
    }
    /**
     * @param Node|Node[] $nodes
     */
    public function printNodes($nodes) : void
    {
        $dumpedNodesContents = SimpleNodeDumper::dump($nodes);
        // colorize
        $colorContents = $this->addConsoleColors($dumpedNodesContents);
        $this->symfonyStyle->writeln($colorContents);
        $this->symfonyStyle->newLine();
    }
    private function addConsoleColors(string $contents) : string
    {
        // decorate class names
        $colorContents = Strings::replace($contents, self::CLASS_NAME_REGEX, static function (array $match) : string {
            return '<fg=green>' . $match['class_name'] . '</>(';
        });
        // decorate keys
        return Strings::replace($colorContents, self::PROPERTY_KEY_REGEX, static function (array $match) : string {
            return '<fg=yellow>' . $match['key'] . '</>:';
        });
    }
}
