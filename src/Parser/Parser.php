<?php declare(strict_types=1);

namespace Rector\Parser;

use PhpParser\Node;
use PhpParser\Parser as NikicParser;
use Rector\Contract\Parser\ParserInterface;

final class Parser implements ParserInterface
{
    /**
     * @var NikicParser
     */
    private $nikicParser;

    /**
     * @var Node[][]
     */
    private $nodesByFile = [];

    public function __construct(NikicParser $nikicParser)
    {
        $this->nikicParser = $nikicParser;
    }

    /**
     * @return Node[]
     */
    public function parseFile(string $filePath): array
    {
        if (isset($this->nodesByFile[$filePath])) {
            return $this->nodesByFile[$filePath];
        }

        $fileContent = file_get_contents($filePath);
        $this->nodesByFile[$filePath] = $this->nikicParser->parse($fileContent);


        return $this->nodesByFile[$filePath];
    }
}
