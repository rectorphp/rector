<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use Nette\Utils\Reflection;
use Rector\Core\PhpDoc\PhpDocTagsFinder;

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
    public function extractTagsFromMethodDockblock(string $class, string $method, string $tag): array
    {
        $reflectedMethod = $this->classMethodReflectionFactory->createReflectionMethodIfExists($class, $method);

        if ($reflectedMethod === null) {
            return [];
        }

        $docComment = $reflectedMethod->getDocComment();

        if (! is_string($docComment)) {
            return [];
        }

        $extractedTags = $this->phpDocTagsFinder->extractTagsFromStringedDocblock($docComment, $tag);

        $classes = [];
        foreach ($extractedTags as $returnTag) {
            /** @var class-string $className */
            $className = Reflection::expandClassName($returnTag, $reflectedMethod->getDeclaringClass());
            $classes[] = $className;
        }

        return $classes;
    }
}
