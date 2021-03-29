<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Parser as NikicParser;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

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

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(NikicParser $nikicParser, SmartFileSystem $smartFileSystem)
    {
        $this->nikicParser = $nikicParser;
        $this->smartFileSystem = $smartFileSystem;
    }

    /**
     * @return Node[]
     */
    public function parseContent(string $content): array
    {
        $nodes = $this->nikicParser->parse($content);
        if ($nodes === null) {
            $nodes = [];
        }

        return $nodes;
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

        $fileContent = $this->smartFileSystem->readFile($fileRealPath);

        $nodes = $this->nikicParser->parse($fileContent);
        if ($nodes === null) {
            $nodes = [];
        }

        $this->nodesByFile[$fileRealPath] = $nodes;
        return $this->nodesByFile[$fileRealPath];
    }
}
