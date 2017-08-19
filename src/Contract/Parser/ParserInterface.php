<?php declare(strict_types=1);

namespace Rector\Contract\Parser;

use PhpParser\Node;

interface ParserInterface
{
    /**
     * @return Node[]
     */
    public function parseFile(string $filePath): array;
}
