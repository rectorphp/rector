<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Parser as NikicParser;
use Symplify\SmartFileSystem\SmartFileInfo;

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
    public function parseFileInfo(SmartFileInfo $smartFileInfo): array
    {
        $fileRealPath = $smartFileInfo->getRealPath();

        if (isset($this->nodesByFile[$fileRealPath])) {
            return $this->nodesByFile[$fileRealPath];
        }

        $fileContent = FileSystem::read($fileRealPath);
        $this->nodesByFile[$fileRealPath] = (array) $this->nikicParser->parse($fileContent);

        return $this->nodesByFile[$fileRealPath];
    }
}
