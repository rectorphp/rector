<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PHPStan\Type\ObjectType;
use PHPStan\Reflection\ClassReflection;
use RectorPrefix202307\Nette\Utils\Strings;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ConditionalType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper;
use RectorPrefix202307\Symfony\Contracts\Service\Attribute\Required;
use PHPStan\Type\TypeTraverser;
/**
 * @implements TypeMapperInterface<ConditionalType>
 */
final class ConditionalTypeMapper implements TypeMapperInterface
{
    /**
     * @var \Rector\PHPStanStaticTypeMapper\PHPStanStaticTypeMapper
     */
    private $phpStanStaticTypeMapper;
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
        return ConditionalType::class;
    }
    /**
     * @param ConditionalType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type) : TypeNode
    {
        $type = TypeTraverser::map($type, static function (Type $type, callable $traverse) : Type {
            if ($type instanceof ObjectType && !$type->getClassReflection() instanceof ClassReflection) {
                $newClassName = (string) Strings::after($type->getClassName(), '\\', -1);
                return $traverse(new ObjectType($newClassName));
            }
            return $traverse($type);
        });
        return $type->toPhpDocNode();
    }
    /**
     * @param ConditionalType $type
     * @param TypeKind::* $typeKind
     */
    public function mapToPhpParserNode(Type $type, string $typeKind) : ?Node
    {
        $type = TypeCombinator::union($type->getIf(), $type->getElse());
        return $this->phpStanStaticTypeMapper->mapToPhpParserNode($type, $typeKind);
    }
}
