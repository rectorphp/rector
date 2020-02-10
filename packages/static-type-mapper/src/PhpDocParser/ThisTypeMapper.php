<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\Contract\PhpDocParser\PhpDocTypeMapperInterface;

final class ThisTypeMapper implements PhpDocTypeMapperInterface
{
    public function getNodeType(): string
    {
        return ThisTypeNode::class;
    }

    public function mapToPHPStanType(TypeNode $typeNode, Node $node, NameScope $nameScope): Type
    {
        if ($node === null) {
            throw new ShouldNotHappenException();
        }
        /** @var string $className */
        $className = $node->getAttribute(AttributeKey::CLASS_NAME);

        return new ThisType($className);
    }
}
