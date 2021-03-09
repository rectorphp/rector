<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\Contract\PhpParser;

use PhpParser\Node;
use PHPStan\Type\Type;

interface PhpParserNodeMapperInterface
{
    /**
<<<<<<< HEAD
     * @return class-string<Node>
=======
     * @return class-string<\PhpParser\Node>
>>>>>>> 1b83ff428... add return type class string
     */
    public function getNodeType(): string;

    public function mapToPHPStan(Node $node): Type;
}
