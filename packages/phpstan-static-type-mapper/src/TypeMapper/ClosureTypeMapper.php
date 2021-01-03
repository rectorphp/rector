<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;
use Rector\PhpdocParserPrinter\ValueObject\TypeNode\AttributeAwareCallableTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\PHPStanStaticTypeMapperAwareInterface;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;

final class ClosureTypeMapper implements TypeMapperInterface, PHPStanStaticTypeMapperAwareInterface
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    public function getNodeClass(): string
    {
        return ClosureType::class;
    }

    /**
     * @param ClosureType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        $identifierTypeNode = new IdentifierTypeNode($type->getClassName());

        $returnDocTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($type->getReturnType());

        return new AttributeAwareCallableTypeNode($identifierTypeNode, [], $returnDocTypeNode);
    }

    /**
     * @param ClosureType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        if ($kind === 'property') {
            return null;
        }

        return new Name('callable');
    }

    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        return '\\' . Closure::class;
    }

    public function setPHPStanStaticTypeMapper(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
}
