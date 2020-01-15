<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper\TypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\CallableType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\Exception\NotImplementedException;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class CallableTypeMapper implements TypeMapperInterface
{
    public function getNodeClass(): string
    {
        return CallableType::class;
    }

    /**
     * @param CallableType $type
     */
    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        throw new NotImplementedException();
    }

    /**
     * @param CallableType $type
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        if ($kind === 'property') {
            return null;
        }

        return new Identifier('callable');
    }

    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        return $type->describe(VerbosityLevel::typeOnly());
    }
}
