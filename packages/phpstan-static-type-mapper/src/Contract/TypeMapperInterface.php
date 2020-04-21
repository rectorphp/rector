<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\Contract;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;

interface TypeMapperInterface
{
    public function getNodeClass(): string;

    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode;

    /**
     * @return Name|NullableType|UnionType|null
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node;

    public function mapToDocString(Type $type, ?Type $parentType = null): string;
}
