<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use PHPStan\Type\VoidType;
use Rector\AttributeAwarePhpDoc\Ast\Type\AttributeAwareIdentifierTypeNode;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class VoidTypeMapper implements TypeMapperInterface
{
    /**
     * @var string
     */
    private const VOID = 'void';

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
        return new AttributeAwareIdentifierTypeNode(self::VOID);
    }

    /**
     * @param VoidType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        if (! $this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::VOID_TYPE)) {
            return null;
        }

        if (in_array($kind, ['param', 'property'], true)) {
            return null;
        }

        return new Name(self::VOID);
    }

    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        if ($this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            // the void type is better done in PHP code
            return '';
        }

        // fallback for PHP 7.0 and older, where void type was only in docs
        return self::VOID;
    }
}
