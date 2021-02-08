<?php

declare(strict_types=1);

namespace Rector\DeadCode\UnusedNodeResolver;

use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;

final class UnusedClassResolver
{
    /**
     * @var string[]
     */
    private $cachedUsedClassNames = [];

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(NodeNameResolver $nodeNameResolver, NodeRepository $nodeRepository)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeRepository = $nodeRepository;
    }

    public function isClassWithoutInterfaceAndNotController(Class_ $class): bool
    {
        if ($class->implements !== []) {
            return false;
        }

        if ($class->extends !== null) {
            return false;
        }

        if ($this->nodeNameResolver->isNames($class, ['*Controller', '*Presenter'])) {
            return false;
        }
        return ! $this->nodeNameResolver->isName($class, '*Test');
    }

    public function isClassUsed(Class_ $class): bool
    {
        return $this->nodeNameResolver->isNames($class, $this->getUsedClassNames());
    }

    /**
     * @return string[]
     */
    private function getUsedClassNames(): array
    {
        if (! StaticPHPUnitEnvironment::isPHPUnitRun() && $this->cachedUsedClassNames !== []) {
            return $this->cachedUsedClassNames;
        }

        /** @var string[] $cachedUsedClassNames */
        $cachedUsedClassNames = array_merge(
            $this->getParamNodesClassNames(),
            $this->getNewNodesClassNames(),
            $this->getStaticCallClassNames(),
            $this->getClassConstantFetchNames()
        );

        $cachedUsedClassNames = $this->sortAndUniqueArray($cachedUsedClassNames);

        $this->cachedUsedClassNames = $cachedUsedClassNames;

        return $this->cachedUsedClassNames;
    }

    /**
     * @return string[]
     */
    private function getParamNodesClassNames(): array
    {
        $classNames = [];

        foreach ($this->nodeRepository->getParams() as $param) {
            if ($param->type === null) {
                continue;
            }

            if ($param->type instanceof NullableType) {
                $param = $param->type;
            }

            if ($param->type instanceof Identifier) {
                continue;
            }

            if ($param->type instanceof Name) {
                /** @var string $paramTypeName */
                $paramTypeName = $this->nodeNameResolver->getName($param->type);
                $classNames[] = $paramTypeName;
            } else {
                throw new NotImplementedYetException();
            }
        }

        return $classNames;
    }

    /**
     * @return string[]
     */
    private function getNewNodesClassNames(): array
    {
        $classNames = [];

        foreach ($this->nodeRepository->getNews() as $newNode) {
            $newClassName = $this->nodeNameResolver->getName($newNode->class);
            if (! is_string($newClassName)) {
                continue;
            }

            $classNames[] = $newClassName;
        }

        return $classNames;
    }

    /**
     * @return string[]
     */
    private function getStaticCallClassNames(): array
    {
        $classNames = [];

        foreach ($this->nodeRepository->getStaticCalls() as $staticCallNode) {
            $staticClassName = $this->nodeNameResolver->getName($staticCallNode->class);
            if (! is_string($staticClassName)) {
                continue;
            }

            $classNames[] = $staticClassName;
        }
        return $classNames;
    }

    /**
     * @return string[]
     */
    private function getClassConstantFetchNames(): array
    {
        $classConstFetches = $this->nodeRepository->getClassConstFetches();

        $classNames = [];
        foreach ($classConstFetches as $classConstFetch) {
            $className = $this->nodeNameResolver->getName($classConstFetch->class);
            if (! is_string($className)) {
                continue;
            }

            $classNames[] = $className;
        }

        return $classNames;
    }

    /**
     * @param string[] $items
     * @return string[]
     */
    private function sortAndUniqueArray(array $items): array
    {
        sort($items);
        return array_unique($items);
    }
}
