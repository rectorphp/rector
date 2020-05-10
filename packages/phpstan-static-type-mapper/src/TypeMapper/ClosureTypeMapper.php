<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use Closure;
use PhpParser\Node;
use PhpParser\Node\Name;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ClosureType;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedException;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class ClosureTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return ClosureType::class;
    }

    /**
     * @param ClosureType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        throw new NotImplementedException();
    }

    /**
     * @param ClosureType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        if ($kind === 'property') {
            return null;
        }

        return new Name('callable');
    }

    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        return '\\' . Closure::class;
    }
}
