<?php

namespace Roave\BetterReflection\SourceLocator\Reflection;

use PhpParser\Builder\Class_;
use PhpParser\Builder\Interface_;
use PhpParser\Builder\Method;
use PhpParser\Builder\Property;
use PhpParser\Builder\Trait_;
use PhpParser\Builder\Declaration;
use PhpParser\Comment\Doc;
use ReflectionClass as CoreReflectionClass;
use ReflectionMethod as CoreReflectionMethod;
use ReflectionProperty as CoreReflectionProperty;
use Reflector as CoreReflector;

/**
 * Function that generates a stub source from a given reflection instance.
 *
 * @internal
 */
final class SourceStubber
{
    /**
     * @param Class_|Interface_|Trait_|Method|Property $node
     * @param CoreReflectionClass|CoreReflectionMethod|CoreReflectionProperty $reflection
     */
    private function addDocComment(Declaration $node, CoreReflector $reflection): void
    {
        if (false !== $reflection->getDocComment()) {
            $node->setDocComment(new Doc($reflection->getDocComment()));
        }
    }
}
