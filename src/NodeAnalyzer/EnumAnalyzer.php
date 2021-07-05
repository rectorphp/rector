<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class EnumAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    /**
     * @see https://github.com/myclabs/php-enum#declaration
     */
    public function isEnumClassConst(ClassConst $classConst): bool
    {
        $classLike = $classConst->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        if ($classLike->extends === null) {
            return false;
        }

        return $this->nodeNameResolver->isName($classLike->extends, '*Enum');
    }
}
