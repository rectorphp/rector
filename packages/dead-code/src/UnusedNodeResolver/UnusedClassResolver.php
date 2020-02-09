<?php

declare(strict_types=1);

namespace Rector\DeadCode\UnusedNodeResolver;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\NodeContainer\NodeCollector\ParsedNodeCollector;
use Rector\Core\PhpParser\Node\Resolver\NameResolver;
use Rector\Core\Testing\PHPUnit\PHPUnitEnvironment;

final class UnusedClassResolver
{
    /**
     * @var string[]
     */
    private $cachedUsedClassNames = [];

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ParsedNodeCollector
     */
    private $parsedNodeCollector;

    public function __construct(NameResolver $nameResolver, ParsedNodeCollector $parsedNodeCollector)
    {
        $this->nameResolver = $nameResolver;
        $this->parsedNodeCollector = $parsedNodeCollector;
    }

    /**
     * @return string[]
     */
    public function getUsedClassNames(): array
    {
        if (! PHPUnitEnvironment::isPHPUnitRun() && $this->cachedUsedClassNames !== []) {
            return $this->cachedUsedClassNames;
        }

        $cachedUsedClassNames = array_merge(
            $this->getParamNodesClassNames(),
            $this->getNewNodesClassNames(),
            $this->getStaticCallClassNames(),
            $this->getClassConstantFetchNames()
        );

        $cachedUsedClassNames = $this->sortAndUniqueArray($cachedUsedClassNames);

        return $this->cachedUsedClassNames = $cachedUsedClassNames;
    }

    public function isClassWithoutInterfaceAndNotController(Class_ $class): bool
    {
        if ($class->implements !== []) {
            return false;
        }

        if ($class->extends !== null) {
            return false;
        }

        if ($this->nameResolver->isNames($class, ['*Controller', '*Presenter'])) {
            return false;
        }
        return ! $this->nameResolver->isName($class, '*Test');
    }

    public function isClassUsed(Class_ $class): bool
    {
        return $this->nameResolver->isNames($class, $this->getUsedClassNames());
    }

    /**
     * @return string[]
     */
    private function getParamNodesClassNames(): array
    {
        $classNames = [];

        /** @var Param[] $paramNodes */
        $paramNodes = $this->parsedNodeCollector->getNodesByType(Param::class);
        foreach ($paramNodes as $paramNode) {
            if ($paramNode->type === null) {
                continue;
            }

            if ($paramNode->type instanceof NullableType) {
                $paramNode = $paramNode->type;
            }

            if ($paramNode->type instanceof Identifier) {
                continue;
            }

            if ($paramNode->type instanceof Name) {
                /** @var string $paramTypeName */
                $paramTypeName = $this->nameResolver->getName($paramNode->type);
                $classNames[] = $paramTypeName;
            } else {
                throw new NotImplementedException();
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

        /** @var New_[] $newNodes */
        $newNodes = $this->parsedNodeCollector->getNodesByType(New_::class);
        foreach ($newNodes as $newNode) {
            $newNodeClassName = $this->nameResolver->getName($newNode->class);
            if (! is_string($newNodeClassName)) {
                continue;
            }

            $classNames[] = $newNodeClassName;
        }

        return $classNames;
    }

    /**
     * @return string[]
     */
    private function getStaticCallClassNames(): array
    {
        $classNames = [];

        /** @var StaticCall[] $staticCallNodes */
        $staticCallNodes = $this->parsedNodeCollector->getNodesByType(StaticCall::class);
        foreach ($staticCallNodes as $staticCallNode) {
            $staticClassName = $this->nameResolver->getName($staticCallNode->class);
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
        /** @var ClassConstFetch[] $classConstFetches */
        $classConstFetches = $this->parsedNodeCollector->getNodesByType(ClassConstFetch::class);

        $classNames = [];
        foreach ($classConstFetches as $classConstFetch) {
            $className = $this->nameResolver->getName($classConstFetch->class);
            if (! is_string($className)) {
                continue;
            }

            $classNames[] = $className;
        }

        return $classNames;
    }

    private function sortAndUniqueArray(array $items): array
    {
        sort($items);
        return array_unique($items);
    }
}
