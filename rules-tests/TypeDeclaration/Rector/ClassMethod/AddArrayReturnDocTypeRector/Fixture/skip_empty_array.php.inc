<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpParser\Node\Stmt;
use PhpParser\Parser as NikicParser;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class SkipEmptyArray
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
            $this->nodesByFile[$fileRealPath] = [];
        } else {
            $this->nodesByFile[$fileRealPath] = [];
        }

        return $this->nodesByFile[$fileRealPath];
    }
}
