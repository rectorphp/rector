<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\NodeNameResolver\NodeNameResolver;
final class CollectionPropertyDetector
{
    /**
     * @readonly
     */
    private NodeNameResolver $nodeNameResolver;
    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function detect(Property $property) : bool
    {
        if (!$property->type instanceof Node) {
            return \false;
        }
        // 1. direct type
        if ($this->nodeNameResolver->isName($property->type, DoctrineClass::COLLECTION)) {
            return \true;
        }
        // 2. union type
        if ($property->type instanceof UnionType) {
            $unionType = $property->type;
            foreach ($unionType->types as $unionedType) {
                if ($this->nodeNameResolver->isName($unionedType, DoctrineClass::COLLECTION)) {
                    return \true;
                }
            }
        }
        // 3. nullable type
        if ($property->type instanceof NullableType) {
            $directType = $property->type->type;
            if ($this->nodeNameResolver->isName($directType, DoctrineClass::COLLECTION)) {
                return \true;
            }
        }
        return \false;
    }
}
