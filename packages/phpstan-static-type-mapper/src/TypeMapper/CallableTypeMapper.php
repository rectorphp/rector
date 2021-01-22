<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\CallableType;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareCallableTypeNode;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;

final class CallableTypeMapper implements TypeMapperInterface
{
    /**
     * @var PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;

    /**
     * @required
     */
    public function autowireCallableTypeMapper(PHPStanStaticTypeMapper $phpStanStaticTypeMapper): void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }

    public function getNodeClass(): string
    {
        return CallableType::class;
    }

    /**
     * @param CallableType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        $returnTypeNode = $this->phpStanStaticTypeMapper->mapToPHPStanPhpDocTypeNode($type->getReturnType());

        return new AttributeAwareCallableTypeNode(new IdentifierTypeNode('callable'), [], $returnTypeNode);
    }

    /**
     * @param CallableType|ClosureType $type
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
        return $type->describe(VerbosityLevel::typeOnly());
    }
}
