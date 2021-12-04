<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpDoc;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;

/**
 * @see \Rector\Tests\StaticTypeMapper\PhpDoc\PhpDocTypeMapperTest
 */
final class PhpDocTypeMapper
{
    /**
     * @param PhpDocTypeMapperInterface[] $phpDocTypeMappers
     */
    public function __construct(
        private readonly array $phpDocTypeMappers,
        private readonly TypeNodeResolver $typeNodeResolver
    ) {
    }

    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope): Type
    {
        foreach ($this->phpDocTypeMappers as $phpDocTypeMapper) {
            if (! is_a($typeNode, $phpDocTypeMapper->getNodeType())) {
                continue;
            }

            return $phpDocTypeMapper->mapToPHPStanType($typeNode, $node, $nameScope);
        }

        // fallback to PHPStan resolver
        return $this->typeNodeResolver->resolve($typeNode, $nameScope);
    }
}
