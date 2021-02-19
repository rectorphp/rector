<?php

declare(strict_types=1);

namespace Rector\PHPStanStaticTypeMapper;

use PhpParser\Node;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\UnionType as PhpParserUnionType;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\PHPStanStaticTypeMapper\Contract\PHPStanStaticTypeMapperAwareInterface;
use Rector\PHPStanStaticTypeMapper\Contract\TypeMapperInterface;

final class PHPStanStaticTypeMapper
{
    /**
     * @var string
     */
    public const KIND_PARAM = 'param';

    /**
     * @var string
     */
    public const KIND_PROPERTY = 'property';

    /**
     * @var string
     */
    public const KIND_RETURN = 'return';

    /**
     * @var TypeMapperInterface[]
     */
    private $typeMappers = [];

    /**
     * @param TypeMapperInterface[] $typeMappers
     */
    public function __construct(array $typeMappers)
    {
        foreach ($typeMappers as $typeMapper) {
            if ($typeMapper instanceof PHPStanStaticTypeMapperAwareInterface) {
                $typeMapper->setPHPStanStaticTypeMapper($this);
            }
        }

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

        throw new NotImplementedYetException(__METHOD__ . ' for ' . get_class($type));
    }

    /**
     * @return Name|NullableType|PhpParserUnionType|null
     */
    public function mapToPhpParserNode(Type $type, ?string $kind = null): ?Node
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! is_a($type, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToPhpParserNode($type, $kind);
        }

        throw new NotImplementedYetException(__METHOD__ . ' for ' . get_class($type));
    }

    public function mapToDocString(Type $type, ?Type $parentType = null): string
    {
        foreach ($this->typeMappers as $typeMapper) {
            if (! is_a($type, $typeMapper->getNodeClass(), true)) {
                continue;
            }

            return $typeMapper->mapToDocString($type, $parentType);
        }

        throw new NotImplementedYetException(__METHOD__ . ' for ' . get_class($type));
    }
}
