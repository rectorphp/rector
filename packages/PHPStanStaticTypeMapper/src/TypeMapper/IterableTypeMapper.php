<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\IterableType;
use PHPStan\Type\Type;
use Rector\Exception\NotImplementedException;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class IterableTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return IterableType::class;
    }

    /**
     * @param IterableType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        throw new NotImplementedException();
    }

    /**
     * @param IterableType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        return new Identifier('iterable');
    }
}
