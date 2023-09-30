<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\ValueObject;

use PhpParser\Node\Attribute;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
final class DataProviderNodes
{
    /**
     * @var array<array-key, (Attribute | PhpDocTagNode)>
     * @readonly
     */
    public $nodes;
    /**
     * @param array<array-key, Attribute|PhpDocTagNode> $nodes
     */
    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }
    public function isEmpty() : bool
    {
        return $this->nodes === [];
    }
}
