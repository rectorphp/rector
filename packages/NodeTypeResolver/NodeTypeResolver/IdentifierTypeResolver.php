<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\NodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PHPStan\Type\BooleanType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\Type;
use Rector\NodeTypeResolver\Contract\NodeTypeResolverInterface;
/**
 * @implements NodeTypeResolverInterface<Identifier>
 */
final class IdentifierTypeResolver implements NodeTypeResolverInterface
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeClasses() : array
    {
        return [Identifier::class];
    }
    /**
     * @param Identifier $node
     * @return StringType|BooleanType|IntegerType|FloatType|MixedType
     */
    public function resolve(Node $node) : Type
    {
        if ($node->toLowerString() === 'string') {
            return new StringType();
        }
        if ($node->toLowerString() === 'bool') {
            return new BooleanType();
        }
        if ($node->toLowerString() === 'int') {
            return new IntegerType();
        }
        if ($node->toLowerString() === 'float') {
            return new FloatType();
        }
        return new MixedType();
    }
}
