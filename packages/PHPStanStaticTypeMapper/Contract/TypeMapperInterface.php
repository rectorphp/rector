<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\Contract;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;

/**
 * @template TType of Type
 */
interface TypeMapperInterface
{
    /**
     * @return class-string<Type>
     */
    public function getNodeClass(): string;

    /**
     * @param TType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type, TypeKind $typeKind): TypeNode;

    /**
     * @param TType $type
     * @return Name|ComplexType|null
     */
    public function mapToPhpParserNode(Type $type, TypeKind $typeKind): ?Node;
}
