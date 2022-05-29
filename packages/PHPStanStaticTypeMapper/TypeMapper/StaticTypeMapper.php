<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType;
use Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType;
/**
 * @see \Rector\Tests\NodeTypeResolver\StaticTypeMapper\StaticTypeMapperTest
 *
 * @implements TypeMapperInterface<StaticType>
 */
final class StaticTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
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
        return \PHPStan\Type\StaticType::class;
    }
    /**
     * @param StaticType $type
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type, string $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        return new \PHPStan\PhpDocParser\Ast\Type\ThisTypeNode();
    }
    /**
     * @param StaticType $type
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, string $typeKind) : ?\PhpParser\Node
    {
        // special case, for autocomplete of return type
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\SimpleStaticType) {
            return new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::STATIC);
        }
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType) {
            return new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::SELF);
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::STATIC_RETURN_TYPE)) {
            return new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::STATIC);
        }
        return new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::SELF);
    }
}
