<?php

declare(strict_types=1);

namespace Rector\Order;

use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ArrayType;
use PHPStan\Type\BooleanType;
use PHPStan\Type\CallableType;
use PHPStan\Type\FloatType;
use PHPStan\Type\IntegerType;
use PHPStan\Type\IntersectionType;
use PHPStan\Type\IterableType;
use PHPStan\Type\MixedType;
use PHPStan\Type\StringType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Exception\NotImplementedException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use PHPStan\Type\Type;

final class PropertyRanker
{
    public function rank(Property $property): int
    {
        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return 1;
        }

        $varType = $phpDocInfo->getVarType();
        if ($this->isFive($varType)) {
            return 5;
        }

        if ($varType instanceof ArrayType) {
            return 10;
        }

        if ($varType instanceof IterableType) {
            return 10;
        }

        if ($varType instanceof TypeWithClassName) {
            return 15;
        }

        if ($varType instanceof IntersectionType) {
            return 20;
        }

        if ($varType instanceof UnionType) {
            return 25;
        }

        if ($varType instanceof MixedType) {
            return 30;
        }

        if ($varType instanceof CallableType) {
            return 35;
        }

        throw new NotImplementedException(get_class($varType));
    }

    private function isFive(Type $type): bool
    {
        if ($type instanceof StringType) {
            return true;
        }

        if ($type instanceof IntegerType) {
            return true;
        }

        if ($type instanceof BooleanType) {
            return true;
        }

        return $type instanceof FloatType;
    }

    private function isTen(Type $type): bool
    {
        if ($type instanceof ArrayType) {
            return 10;
        }

        return $type instanceof IterableType;
    }
}
