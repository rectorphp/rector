<?php

declare(strict_types=1);

namespace Rector\Order;

use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeNameResolver\NodeNameResolver;

final class StmtVisibilitySorter
{
    /**
     * @var string
     */
    private const VISIBILITY = 'visibility';

    /**
     * @var string
     */
    private const POSITION = 'position';

    /**
     * @var string
     */
    private const NAME = 'name';

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param Class_|Trait_ $classLike
     * @return array<string,array<string, mixed>>
     */
    public function sortProperties(ClassLike $classLike): array
    {
        $properties = [];
        foreach ($classLike->stmts as $position => $propertyStmt) {
            if (! $propertyStmt instanceof Property) {
                continue;
            }

            /** @var string $propertyName */
            $propertyName = $this->nodeNameResolver->getName($propertyStmt);

            $properties[$propertyName][self::NAME] = $propertyName;
            $properties[$propertyName][self::VISIBILITY] = $this->getVisibilityLevelOrder($propertyStmt);
            $properties[$propertyName]['static'] = $propertyStmt->isStatic();
            $properties[$propertyName][self::POSITION] = $position;
        }

        uasort(
            $properties,
            function (array $firstArray, array $secondArray): int {
                return [
                    $firstArray[self::VISIBILITY],
                    $firstArray['static'],
                    $firstArray[self::POSITION],
                ] <=> [$secondArray[self::VISIBILITY], $secondArray['static'], $secondArray[self::POSITION]];
            }
        );

        return $properties;
    }

    /**
     * @return array<string,array<string, mixed>>
     */
    public function sortMethods(ClassLike $classLike): array
    {
        $classMethods = [];
        foreach ($classLike->stmts as $position => $classStmt) {
            if (! $classStmt instanceof ClassMethod) {
                continue;
            }

            /** @var string $classMethodName */
            $classMethodName = $this->nodeNameResolver->getName($classStmt);

            $classMethods[$classMethodName][self::NAME] = $classMethodName;
            $classMethods[$classMethodName][self::VISIBILITY] = $this->getVisibilityLevelOrder($classStmt);
            $classMethods[$classMethodName]['abstract'] = $classStmt->isAbstract();
            $classMethods[$classMethodName]['final'] = $classStmt->isFinal();
            $classMethods[$classMethodName]['static'] = $classStmt->isStatic();
            $classMethods[$classMethodName][self::POSITION] = $position;
        }

        uasort(
            $classMethods,
            function (array $firstArray, array $secondArray): int {
                return [
                    $firstArray[self::VISIBILITY],
                    $firstArray['static'],
                    $secondArray['abstract'],
                    $firstArray['final'],
                    $firstArray[self::POSITION],
                ] <=> [
                    $secondArray[self::VISIBILITY],
                    $secondArray['static'],
                    $firstArray['abstract'],
                    $secondArray['final'],
                    $secondArray[self::POSITION],
                ];
            }
        );

        return $classMethods;
    }

    /**
     * @return array<string,array<string, mixed>>
     */
    public function sortConstants(ClassLike $classLike): array
    {
        $constants = [];
        foreach ($classLike->stmts as $position => $constantStmt) {
            if (! $constantStmt instanceof ClassConst) {
                continue;
            }

            /** @var string $constantName */
            $constantName = $this->nodeNameResolver->getName($constantStmt);

            $constants[$constantName][self::NAME] = $constantName;
            $constants[$constantName][self::VISIBILITY] = $this->getVisibilityLevelOrder($constantStmt);
            $constants[$constantName][self::POSITION] = $position;
        }

        uasort(
            $constants,
            function (array $firstArray, array $secondArray): int {
                return [
                    $firstArray[self::VISIBILITY],
                    $firstArray[self::POSITION],
                ] <=> [$secondArray[self::VISIBILITY], $secondArray[self::POSITION]];
            }
        );

        return $constants;
    }

    /**
     * @param ClassMethod|Property|ClassConst $stmt
     */
    private function getVisibilityLevelOrder(Stmt $stmt): int
    {
        if ($stmt->isPrivate()) {
            return 2;
        }

        if ($stmt->isProtected()) {
            return 1;
        }

        return 0;
    }
}
