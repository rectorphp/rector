<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use Nette\Utils\Reflection;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagsFinder;
use ReflectionMethod;

final class ClassMethodReflectionHelper
{
    /**
     * @var ClassMethodReflectionFactory
     */
    private $classMethodReflectionFactory;

    /**
     * @var PhpDocTagsFinder
     */
    private $phpDocTagsFinder;

    public function __construct(
        ClassMethodReflectionFactory $classMethodReflectionFactory,
        PhpDocTagsFinder $phpDocTagsFinder
    ) {
        $this->classMethodReflectionFactory = $classMethodReflectionFactory;
        $this->phpDocTagsFinder = $phpDocTagsFinder;
    }

    /**
     * @return array<class-string>
     */
    private function extractTagsFromMethodDocBlock(string $class, string $method): array
    {
        $reflectedMethod = $this->classMethodReflectionFactory->createReflectionMethodIfExists($class, $method);
        if (! $reflectedMethod instanceof ReflectionMethod) {
            return [];
        }

        $docComment = $reflectedMethod->getDocComment();

        if (! is_string($docComment)) {
            return [];
        }

        $throwsTypes = $this->phpDocTagsFinder->extractTrowsTypesFromDocBlock($docComment);

        $classes = [];
        foreach ($throwsTypes as $returnTag) {
            /** @var class-string $className */
            $className = Reflection::expandClassName($returnTag, $reflectedMethod->getDeclaringClass());
            $classes[] = $className;
        }

        return $classes;
    }
}
