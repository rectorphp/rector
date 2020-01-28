<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDocParser;

use PhpParser\Node;
use PHPStan\Analyser\NameScope;
use PHPStan\PhpDocParser\Ast\Type\ThisTypeNode;
use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\Type\ThisType;
use PHPStan\Type\Type;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Contract\PhpDocParser\PhpDocTypeMapperInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;

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
