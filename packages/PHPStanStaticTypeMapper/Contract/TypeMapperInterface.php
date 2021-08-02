<?php

declare(strict_types=1);

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
    public function getNodeClass(): string;

    /**
     * @param T $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, TypeKind $typeKind): TypeNode;

    /**
     * @param T $type
     * @return Name|NullableType|UnionType|null
     */
    public function mapToPhpParserNode(Type $type, TypeKind $typeKind): ?Node;
}
