<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\PhpDoc;

use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareArrayTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareFullyQualifiedIdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareUnionTypeNode;

final class CollectionTypeFactory
{
    public function createFromIdentifierType(IdentifierTypeNode $identifierTypeNode): AttributeAwareUnionTypeNode
    {
        $genericTypeNode = $this->createGenericTypeNode($identifierTypeNode);

        return new AttributeAwareUnionTypeNode([
            $genericTypeNode,
            new AttributeAwareArrayTypeNode($identifierTypeNode),
        ]);
    }

    private function createGenericTypeNode(IdentifierTypeNode $identifierTypeNode): GenericTypeNode
    {
        $genericTypesNodes = [new AttributeAwareIdentifierTypeNode('int'), $identifierTypeNode];

        return new GenericTypeNode(new AttributeAwareFullyQualifiedIdentifierTypeNode(
            'Doctrine\Common\Collections\Collection'
        ), $genericTypesNodes);
    }
}
