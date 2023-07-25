<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Node as AstNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\ObjectWithoutClassType;
use PHPStan\Type\Type;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\PhpDocParser\PhpDocParser\PhpDocNodeTraverser;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix202307\Symfony\Contracts\Service\Attribute\Required;
/**
 * @implements TypeMapperInterface<IntersectionType>
 */
final class IntersectionTypeMapper implements TypeMapperInterface
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
    public function __construct(PhpVersionProvider $phpVersionProvider, ReflectionProvider $reflectionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
        $this->reflectionProvider = $reflectionProvider;
    }
    /**
     * @required
     */
    public function autowire(PHPStanStaticTypeMapper $phpStanStaticTypeMapper) : void
    {
        $this->phpStanStaticTypeMapper = $phpStanStaticTypeMapper;
    }
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string
    {
        return IntersectionType::class;
    }
    /**
     * @param IntersectionType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        $typeNode = $type->toPhpDocNode();
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->traverseWithCallable($typeNode, '', static function (AstNode $astNode) : ?IdentifierTypeNode {
            if ($astNode instanceof IdentifierTypeNode) {
                $astNode->name = '\\' . $astNode->name;
                return $astNode;
            }
            return null;
        });
        return $typeNode;
    }
    /**
     * @param IntersectionType $type
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        if (!$this->phpVersionProvider->isAtLeastPhpVersion(PhpVersionFeature::INTERSECTION_TYPES)) {
            return null;
        }
        $intersectionedTypeNodes = [];
        foreach ($type->getTypes() as $intersectionedType) {
            $resolvedType = $this->phpStanStaticTypeMapper->mapToPhpParserNode($intersectionedType, $typeKind);
            if (!$resolvedType instanceof Name && !$resolvedType instanceof Identifier) {
                return null;
            }
            $resolvedTypeName = (string) $resolvedType;
            /**
             * ObjectWithoutClassType can happen when use along with \PHPStan\Type\Accessory\HasMethodType
             * Use "object" as returned type
             */
            if ($intersectionedType instanceof ObjectWithoutClassType) {
                return $resolvedType;
            }
            if (!$intersectionedType instanceof ObjectType) {
                return null;
            }
            if (!$this->reflectionProvider->hasClass($resolvedTypeName)) {
                return null;
            }
            $intersectionedTypeNodes[] = $resolvedType;
        }
        if ($intersectionedTypeNodes === []) {
            return null;
        }
        if (\count($intersectionedTypeNodes) === 1) {
            return \current($intersectionedTypeNodes);
        }
        return new Node\IntersectionType($intersectionedTypeNodes);
    }
}
