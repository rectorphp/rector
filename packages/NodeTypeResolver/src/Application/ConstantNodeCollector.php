<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Application;

use Nette\Utils\Strings;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\ClassConst;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
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

    /**
     * @var string[][][]
     */
    private $classConstantFetchByClassAndName = [];

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var ClassLikeNodeCollector
     */
    private $classLikeNodeCollector;

    public function __construct(
        NameResolver $nameResolver,
        NodeTypeResolver $nodeTypeResolver,
        ClassLikeNodeCollector $classLikeNodeCollector
    ) {
        $this->nameResolver = $nameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->classLikeNodeCollector = $classLikeNodeCollector;
    }

    public function addClassConstant(ClassConst $classConst): void
    {
        $className = $classConst->getAttribute(Attribute::CLASS_NAME);
        if ($className === null) {
            throw new ShouldNotHappenException();
        }

        $constantName = $this->nameResolver->resolve($classConst);

        $this->constantsByType[$className][$constantName] = $classConst;
    }

    public function addClassConstantFetch(ClassConstFetch $classConstFetch): void
    {
        $constantName = $this->nameResolver->resolve($classConstFetch->name);
        if ($constantName === 'class' || $constantName === null) {
            // this is not a manual constant
            return;
        }

        $className = $this->nameResolver->resolve($classConstFetch->class);

        if (in_array($className, ['static', 'self', 'parent'], true)) {
            $resolvedClassTypes = $this->nodeTypeResolver->resolve($classConstFetch->class);

            $className = $this->matchClassTypeThatContainsConstant($resolvedClassTypes, $constantName);
            if ($className === null) {
                return;
            }
        } else {
            $resolvedClassTypes = $this->nodeTypeResolver->resolve($classConstFetch->class);
            $className = $this->matchClassTypeThatContainsConstant($resolvedClassTypes, $constantName);
            if ($className === null) {
                return;
            }
        }

        // current class
        $classOfUse = $classConstFetch->getAttribute(Attribute::CLASS_NAME);

        $this->classConstantFetchByClassAndName[$className][$constantName][] = $classOfUse;

        $this->classConstantFetchByClassAndName[$className][$constantName] = array_unique(
            $this->classConstantFetchByClassAndName[$className][$constantName]
        );
    }

    public function findClassConstant(string $className, string $constantName): ?ClassConst
    {
        if (Strings::contains($constantName, '\\')) {
            throw new ShouldNotHappenException(sprintf('Switched arguments in "%s"', __METHOD__));
        }

        return $this->constantsByType[$className][$constantName] ?? null;
    }

    /**
     * @return string[]|null
     */
    public function findClassConstantFetches(string $className, string $constantName): ?array
    {
        return $this->classConstantFetchByClassAndName[$className][$constantName] ?? null;
    }

    /**
     * @param string[] $resolvedClassTypes
     */
    private function matchClassTypeThatContainsConstant(array $resolvedClassTypes, string $constant): ?string
    {
        foreach ($resolvedClassTypes as $resolvedClassType) {
            $classOrInterface = $this->classLikeNodeCollector->findClassOrInterface($resolvedClassType);
            if ($classOrInterface === null) {
                continue;
            }

            foreach ($classOrInterface->stmts as $stmt) {
                if (! $stmt instanceof ClassConst) {
                    continue;
                }

                if ($this->nameResolver->isName($stmt, $constant)) {
                    return $resolvedClassType;
                }
            }
        }

        return null;
    }
}
