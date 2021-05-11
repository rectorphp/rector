<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Node\Stmt;
use PhpParser\Parser as NikicParser;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class Parser
{
    /**
     * @var array<string, Stmt[]>
     */
    private array $nodesByFile = [];

    public function __construct(
        private NikicParser $nikicParser,
        private SmartFileSystem $smartFileSystem
    ) {
    }

    /**
     * @return Stmt[]
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
