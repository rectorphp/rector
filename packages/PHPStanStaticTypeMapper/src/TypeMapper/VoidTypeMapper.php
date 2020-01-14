<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\ValueObject\PhpVersionFeature;

final class VoidTypeMapper implements TypeMapperInterface
{
    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }

    public function getNodeClass(): string
    {
        return VoidType::class;
    }

    /**
     * @param VoidType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return new IdentifierTypeNode('void');
    }

    /**
     * @param VoidType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        if (! $this->phpVersionProvider->isAtLeast(PhpVersionFeature::VOID_TYPE)) {
            return null;
        }

        if (in_array($kind, ['param', 'property'], true)) {
            return null;
        }

        return new Identifier('void');
    }
}
