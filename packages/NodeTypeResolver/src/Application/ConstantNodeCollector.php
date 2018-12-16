<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Application;

use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Resolver\NameResolver;

final class ConstantNodeCollector
{
    /**
     * @var ClassConst[][]
     */
    private $constantsByType = [];

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    public function addConstant(ClassConst $classConst): void
    {
        $className = (string) $classConst->getAttribute(Attribute::CLASS_NAME);
        $constantName = $this->nameResolver->resolve($classConst);

        $this->constantsByType[$className][$constantName] = $classConst;
    }

    public function findConstant(string $constantName, string $className): ?ClassConst
    {
        return $this->constantsByType[$className][$constantName] ?? null;
    }
}
