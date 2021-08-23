<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\Contract;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
/**
 * @template T of Type
 */
interface TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass() : string;
    /**
     * @param \PHPStan\Type\Type $type
     * @param \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind $typeKind
     */
    public function mapToPHPStanPhpDocTypeNode($type, $typeKind) : \PHPStan\PhpDocParser\Ast\Type\TypeNode;
    /**
     * @param \PHPStan\Type\Type $type
     * @return Name|NullableType|UnionType|null
     * @param \Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind $typeKind
     */
    public function mapToPhpParserNode($type, $typeKind) : ?\PhpParser\Node;
}
