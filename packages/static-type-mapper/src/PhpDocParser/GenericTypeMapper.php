<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDoc\TypeNodeResolver;
use PHPStan\PhpDocParser\Ast\Type\GenericTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\Type;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;

final class GenericTypeMapper implements PhpDocTypeMapperInterface
{
    /**
     * @var TypeNodeResolver
     */
    private $typeNodeResolver;

    public function __construct(TypeNodeResolver $typeNodeResolver)
    {
        $this->typeNodeResolver = $typeNodeResolver;
    }

    public function getNodeType(): string
    {
        return GenericTypeNode::class;
    }

    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope): Type
    {
        return $this->typeNodeResolver->resolve($typeNode, $nameScope);
    }
}
