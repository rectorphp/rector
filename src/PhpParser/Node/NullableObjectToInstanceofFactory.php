<?php

declare (strict_types=1);
namespace Rector\PhpParser\Node;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
use Rector\StaticTypeMapper\ValueObject\Type\ShortenedObjectType;
final class NullableObjectToInstanceofFactory
{
    /**
     * @return \PhpParser\Node\Expr\BooleanNot|\PhpParser\Node\Expr\Instanceof_
     */
    public function create(Expr $expr, ObjectType $objectType, bool $isNegated)
    {
        $className = $objectType instanceof ShortenedObjectType || $objectType instanceof AliasedObjectType ? $objectType->getFullyQualifiedName() : $objectType->getClassName();
        $instanceof = new Instanceof_($expr, new FullyQualified($className));
        return $isNegated ? new BooleanNot($instanceof) : $instanceof;
    }
}
