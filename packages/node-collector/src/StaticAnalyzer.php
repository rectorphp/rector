<?php

declare(strict_types=1);

namespace Rector\NodeCollector;

use Nette\Utils\Strings;
use Rector\NodeCollector\NodeCollector\NodeRepository;
use Rector\NodeTypeResolver\ClassExistenceStaticHelper;
use ReflectionClass;

final class StaticAnalyzer
{
    /**
     * @var NodeRepository
     */
    private $nodeRepository;

    public function __construct(NodeRepository $nodeRepository)
    {
        $this->nodeRepository = $nodeRepository;
    }

    public function isStaticMethod(string $methodName, string $className): bool
    {
        $classMethod = $this->nodeRepository->findClassMethod($className, $methodName);
        if ($classMethod !== null) {
            return $classMethod->isStatic();
        }

        // could be static in doc type magic
        // @see https://regex101.com/r/tlvfTB/1
        if (! ClassExistenceStaticHelper::doesClassLikeExist($className)) {
            return false;
        }

        $reflectionClass = new ReflectionClass($className);
        if ($this->hasStaticAnnotation($methodName, $reflectionClass)) {
            return true;
        }

        // probably magic method → we don't know
        if (! method_exists($className, $methodName)) {
            return false;
        }

        $methodReflection = $reflectionClass->getMethod($methodName);

        return $methodReflection->isStatic();
    }

    private function hasStaticAnnotation(string $methodName, ReflectionClass $reflectionClass): bool
    {
        return (bool) Strings::match(
            (string) $reflectionClass->getDocComment(),
            '#@method\s*static\s*(.*?)\b' . $methodName . '\b#'
        );
    }
}
