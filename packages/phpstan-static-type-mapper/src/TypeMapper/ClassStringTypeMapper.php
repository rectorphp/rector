<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ClassStringType;
use PHPStan\Type\Generic\GenericClassStringType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareGenericTypeNode;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\PHPStanStaticTypeMapperAwareInterface;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;

final class ClassStringTypeMapper implements TypeMapperInterface, PHPStanStaticTypeMapperAwareInterface
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    public function getNodeClass(): string
    {
        return ClassStringType::class;
    }

    /**
     * @param ClassStringType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        $attributeAwareIdentifierTypeNode = new AttributeAwareIdentifierTypeNode('class-string');

        if ($type instanceof GenericClassStringType) {
            $genericTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($type->getGenericType());
            return new AttributeAwareGenericTypeNode($attributeAwareIdentifierTypeNode, [$genericTypeNode]);
        }

        return $attributeAwareIdentifierTypeNode;
    }

    /**
     * @param ClassStringType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        return null;
    }

    /**
     * @param ClassStringType $type
     */
    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        return $type->describe(VerbosityLevel::typeOnly());
    }

    public function setPHPStanStaticTypeMapper(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
}
