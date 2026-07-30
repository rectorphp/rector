<?php

declare (strict_types=1);
namespace Rector\PHPStanStaticTypeMapper\Contract;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\Identifier;
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
     * A mapper can cover several types at once, when they all map to the same output,
     * e.g. every accessory string type maps to "string"
     *
     * @return array<class-string<TType>>
     */
    public function getNodeClasses(): array;
    /**
     * @param TType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode;
    /**
     * @param TType $type
     * @param TypeKind::* $typeKind
     * @return Name|ComplexType|Identifier|null
     */
    public function mapToPhpParserNode(Type $type, string $typeKind): ?Node;
}
