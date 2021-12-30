<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\BooleanType;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\Type;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
/**
 * @implements TypeMapperInterface<BooleanType>
 */
final class BooleanTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return \PHPStan\Type\BooleanType::class;
    }
    /**
     * @param BooleanType $type
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        if ($this->isFalseBooleanTypeWithUnion($type)) {
            return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('false');
        }
        if ($type instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            // cannot be parent of union
            return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('true');
        }
        return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('bool');
    }
    /**
     * @param BooleanType $type
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind) : ?\PhpParser\Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }
        if ($this->isFalseBooleanTypeWithUnion($type)) {
            return new \PhpParser\Node\Name('false');
        }
        return new \PhpParser\Node\Name('bool');
    }
    private function isFalseBooleanTypeWithUnion(\PHPStan\Type\Type $type) : bool
    {
        if (!$type instanceof \PHPStan\Type\Constant\ConstantBooleanType) {
            return \false;
        }
        if ($type->getValue()) {
            return \false;
        }
        return $this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::UNION_TYPES);
    }
}
