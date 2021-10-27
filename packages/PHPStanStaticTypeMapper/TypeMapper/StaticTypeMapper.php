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
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType;
/**
 * @see \Rector\Tests\NodeTypeResolver\StaticTypeMapper\StaticTypeMapperTest
 *
 * @implements TypeMapperInterface<StaticType>
 */
final class StaticTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
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
     * @param \PHPStan\Type\Type $type
     * @param \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind
     */
    public function mapToPHPStanPhpDocTypeNode($type, $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        return new \PHPStan\PhpDocParser\Ast\Type\ThisTypeNode();
    }
    /**
     * @param \PHPStan\Type\Type $type
     * @param \Rector\PHPStanStaticTypeMapper\Enum\TypeKind $typeKind
     */
    public function mapToPhpParserNode($type, $typeKind) : ?\PhpParser\Node
    {
        if ($type instanceof \Rector\StaticTypeMapper\ValueObject\Type\SelfStaticType) {
            return new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::SELF()->getValue());
        }
        if ($this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::STATIC_RETURN_TYPE)) {
            return new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::STATIC()->getValue());
        }
        return new \PhpParser\Node\Name(\Rector\Core\Enum\ObjectReference::SELF()->getValue());
    }
}
