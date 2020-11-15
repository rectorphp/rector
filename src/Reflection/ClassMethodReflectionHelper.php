<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use Nette\Utils\Reflection;
use Rector\Core\PhpDoc\PhpDocTagsFinder;
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
    public function extractReturnTypes(string $class, string $method): array
    {
        $docBlock = $this->resolveDocBlock($class, $method);
        if ($docBlock === null) {
            return [];
        }

        $extractedTags = $this->phpDocTagsFinder->extractReturnTypesFromDocBlock($docBlock);
        $reflectedMethod = new ReflectionMethod($class, $method);
        return $this->expandShortClasses($extractedTags, $reflectedMethod);
    }

    /**
     * @return array<class-string>
     */
    public function extractTagsFromMethodDocBlock(string $class, string $method, string $tag): array
    {
        $reflectedMethod = $this->classMethodReflectionFactory->createReflectionMethodIfExists($class, $method);
        if ($reflectedMethod === null) {
            return [];
        }

        $docComment = $reflectedMethod->getDocComment();

        if (! is_string($docComment)) {
            return [];
        }

        $extractedTags = $this->phpDocTagsFinder->extractTrowsTypesFromDocBlock($docComment, $tag);

        $classes = [];
        foreach ($extractedTags as $returnTag) {
            /** @var class-string $className */
            $className = Reflection::expandClassName($returnTag, $reflectedMethod->getDeclaringClass());
            $classes[] = $className;
        }

        return $classes;
    }

    private function resolveDocBlock(string $class, string $method): ?string
    {
        $reflectedMethod = $this->classMethodReflectionFactory->createReflectionMethodIfExists($class, $method);
        if ($reflectedMethod === null) {
            return null;
        }

        return $reflectedMethod->getDocComment() ?: null;
    }

    private function expandShortClasses(array $extractedTags, $reflectedMethod): array
    {
        $classes = [];
        foreach ($extractedTags as $returnTag) {
            /** @var class-string $className */
            $className = Reflection::expandClassName($returnTag, $reflectedMethod->getDeclaringClass());
            $classes[] = $className;
        }
        return $classes;
    }
}
