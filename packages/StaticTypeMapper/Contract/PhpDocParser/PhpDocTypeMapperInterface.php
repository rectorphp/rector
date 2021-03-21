<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\Contract\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;

interface PhpDocTypeMapperInterface
{
    /**
     * @return class-string<TypeNode>
     */
    public function getNodeType(): string;

    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope): Type;
}
