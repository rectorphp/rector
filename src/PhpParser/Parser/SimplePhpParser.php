<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NodeConnectingVisitor;
use PhpParser\Parser;
use Symplify\SmartFileSystem\SmartFileSystem;

final class SimplePhpParser
{
    public function __construct(
        private Parser $parser,
        private SmartFileSystem $smartFileSystem
    ) {
    }

    /**
     * @return Node[]
     */
    public function parseFile(string $filePath): array
    {
        $fileContent = $this->smartFileSystem->readFile($filePath);
        $nodes = $this->parser->parse($fileContent);

        if ($nodes === null) {
            return [];
        }

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NodeConnectingVisitor());

        return $nodeTraverser->traverse($nodes);
    }
}
