<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Type;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\FalseBooleanType;

final class BooleanTypeMapper implements TypeMapperInterface
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
        return BooleanType::class;
    }

    /**
     * @param BooleanType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        if ($this->isFalseBooleanTypeWithUnion($type)) {
            return new AttributeAwareIdentifierTypeNode('false');
        }

        return new AttributeAwareIdentifierTypeNode('bool');
    }

    /**
     * @param BooleanType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }

        if ($this->isFalseBooleanTypeWithUnion($type)) {
            return new Name('false');
        }

        return new Name('bool');
    }

    /**
     * @param BooleanType $type
     */
    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        if ($this->isFalseBooleanTypeWithUnion($type)) {
            return 'false';
        }

        return 'bool';
    }

    private function isFalseBooleanTypeWithUnion(Type $type): bool
    {
        if (! $type instanceof FalseBooleanType) {
            return false;
        }

        return $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::UNION_TYPES);
    }
}
