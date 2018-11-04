<?php declare(strict_types=1);

namespace Rector\PhpParser\Parser;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Parser as NikicParser;

final class Parser
{
    /**
     * @var Stmt[][]
     */
    private $nodesByFile = [];

    /**
     * @var NikicParser
     */
    private $nikicParser;

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

        $fileContent = FileSystem::read($filePath);
        $this->nodesByFile[$filePath] = (array) $this->nikicParser->parse($fileContent);

        return $this->nodesByFile[$filePath];
    }
}
