<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Accessory\AccessoryLiteralStringType;
use PHPStan\Type\Accessory\AccessoryNonEmptyStringType;
use PHPStan\Type\Accessory\AccessoryNonFalsyStringType;
use PHPStan\Type\Accessory\AccessoryNumericStringType;
use PHPStan\Type\Type;
use Rector\Php\PhpVersionProvider;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\ValueObject\PhpVersionFeature;
/**
 * Every accessory string type narrows "string" with an extra guarantee, so they all map back to "string"
 *
 * @implements TypeMapperInterface<AccessoryLiteralStringType|AccessoryNonEmptyStringType|AccessoryNonFalsyStringType|AccessoryNumericStringType>
 */
final class AccessoryStringTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     */
    private PhpVersionProvider $phpVersionProvider;
    public function __construct(PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @return array<class-string<Type>>
     */
    public function getNodeClasses(): array
    {
        return [AccessoryLiteralStringType::class, AccessoryNonEmptyStringType::class, AccessoryNonFalsyStringType::class, AccessoryNumericStringType::class];
    }
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        return $type->toPhpDocNode();
    }
    public function mapToPhpParserNode(Type $type, string $typeKind): ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }
        return new Identifier('string');
    }
}
