<?php

declare (strict_types=1);
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
    public function getNodeType() : string;
    /**
     * @param \RectorPrefix20210822\PHPStan\PhpDocParser\Ast\Type\TypeNode $typeNode
     * @param \RectorPrefix20210822\PhpParser\Node $node
     * @param \RectorPrefix20210822\PHPStan\Analyser\NameScope $nameScope
     */
    public function mapToPHPStanType($typeNode, $node, $nameScope) : \PHPStan\Type\Type;
}
