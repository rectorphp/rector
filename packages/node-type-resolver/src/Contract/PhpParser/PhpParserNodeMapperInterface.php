<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\Contract\PhpParser;

use PhpParser\Node;
use PHPStan\Type\Type;

interface PhpParserNodeMapperInterface
{
    public function getNodeType(): string;

    public function mapToPHPStan(Node $node): Type;
}
