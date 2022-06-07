<?php

declare (strict_types=1);
namespace Rector\Core\NodeAnalyzer;

use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
final class EnumAnalyzer
{
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NodeNameResolver $nodeNameResolver, BetterNodeFinder $betterNodeFinder)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    /**
     * @see https://github.com/myclabs/php-enum#declaration
     */
    public function isEnumClassConst(ClassConst $classConst) : bool
    {
        $class = $this->betterNodeFinder->findParentType($classConst, Class_::class);
        if (!$class instanceof Class_) {
            return \false;
        }
        if (!$class->extends instanceof Name) {
            return \false;
        }
        return $this->nodeNameResolver->isName($class->extends, '*Enum');
    }
}
