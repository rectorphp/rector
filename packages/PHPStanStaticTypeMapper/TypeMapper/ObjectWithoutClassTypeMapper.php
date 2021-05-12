<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Generic\TemplateObjectWithoutClassType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\ValueObject\Type\EmptyGenericTypeNode;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
final class ObjectWithoutClassTypeMapper implements \Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
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
        return \PHPStan\Type\ObjectWithoutClassType::class;
    }
    /**
     * @param ObjectWithoutClassType $type
     */
    public function mapToPHPStanPhpDocTypeNode(\PHPStan\Type\Type $type) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        if ($type instanceof \PHPStan\Type\Generic\TemplateObjectWithoutClassType) {
            $attributeAwareIdentifierTypeNode = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($type->getName());
            return new \Rector\BetterPhpDocParser\ValueObject\Type\EmptyGenericTypeNode($attributeAwareIdentifierTypeNode);
        }
        return new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode('object');
    }
    /**
     * @param ObjectWithoutClassType $type
     */
    public function mapToPhpParserNode(\PHPStan\Type\Type $type, ?string $kind = null) : ?\PhpParser\Node
    {
        $subtractedType = $type->getSubtractedType();
        if ($subtractedType !== null) {
            return $this->phpStanStaticTypeMapper->mapToPhpParserNode($subtractedType);
        }
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::OBJECT_TYPE)) {
            return null;
        }
        return new \PhpParser\Node\Name('object');
    }
    /**
     * @required
     */
    public function autowireObjectWithoutClassTypeMapper(\Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper $phpStanStaticTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
}
