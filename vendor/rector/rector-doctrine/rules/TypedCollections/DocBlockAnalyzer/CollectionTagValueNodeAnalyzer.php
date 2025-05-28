<?php

declare (strict_types=1);
namespace Rector\Doctrine\TypedCollections\DocBlockAnalyzer;

use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use Rector\Doctrine\Enum\DoctrineClass;
final class CollectionTagValueNodeAnalyzer
{
    /**
     * @param \PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode|\PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode $tagValueNode
     */
    public function detect($tagValueNode) : bool
    {
        if (!$tagValueNode->type instanceof GenericTypeNode) {
            return \false;
        }
        $genericTypeNode = $tagValueNode->type;
        if ($genericTypeNode->type->name === 'Collection') {
            return \true;
        }
        return $genericTypeNode->type->name === DoctrineClass::COLLECTION;
    }
}
