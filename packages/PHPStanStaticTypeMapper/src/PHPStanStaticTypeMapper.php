<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\StaticType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Rector\Exception\NotImplementedException;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class PHPStanStaticTypeMapper
{
    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers = [];

    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(array $typeMappers)
    {
        $this->typeMappers = $typeMappers;
    }

    public function mapToPHPStanPhpDocTypeNode(Type $type): TypeNode
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! is_a($type, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToPHPStanPhpDocTypeNode($type);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($type));
    }

    /**
     * @return Identifier|Name|NullableType|PhpParserUnionType|null
     */
    public function mapToPhpParserNode(Type $phpStanType, ?string $kind = null): ?Node
    {
        foreach ($this->typeMappers as $typeMapper) {
            // it cannot be is_a for SelfObjectType, because type classes inherit from each other
            if (! is_a($phpStanType, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToPhpParserNode($phpStanType, $kind);
        }

        if ($phpStanType instanceof StaticType) {
            return null;
        }

        if ($phpStanType instanceof TypeWithClassName) {
            $lowerCasedClassName = strtolower($phpStanType->getClassName());
            if ($lowerCasedClassName === 'self') {
                return new Identifier('self');
            }

            if ($lowerCasedClassName === 'static') {
                return null;
            }

            if ($lowerCasedClassName === 'mixed') {
                return null;
            }

            return new FullyQualified($phpStanType->getClassName());
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpStanType));
    }

    public function mapToDocString(Type $phpStanType, ?Type $parentType = null): string
    {
        foreach ($this->typeMappers as $typeMapper) {
            // it cannot be is_a for SelfObjectType, because type classes inherit from each other
            if (! is_a($phpStanType, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToDocString($phpStanType, $parentType);
        }

        throw new NotImplementedException(__METHOD__ . ' for ' . get_class($phpStanType));
    }
}
