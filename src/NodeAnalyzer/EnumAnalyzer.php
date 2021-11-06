<?php

declare(strict_types=1);

namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;

final class EnumAnalyzer
{
    public function __construct(
        private NodeNameResolver $nodeNameResolver,
        private BetterNodeFinder $betterNodeFinder,
    ) {
    }

    /**
     * @see https://github.com/myclabs/php-enum#declaration
     */
    public function isEnumClassConst(ClassConst $classConst): bool
    {
        $class = $this->betterNodeFinder->findParentType($classConst, Class_::class);
        if (! $class instanceof Class_) {
            return false;
        }

        if ($class->extends === null) {
            return false;
        }

        return $this->nodeNameResolver->isName($class->extends, '*Enum');
    }
}
