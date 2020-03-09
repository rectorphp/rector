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

    public function extractTagsFromMethodDockblock(string $class, string $method, string $tag): array
    {
        $reflectedMethod = $this->classMethodReflectionFactory->createReflectionMethodIfExists($class, $method);

        if ($reflectedMethod === null) {
            return [];
        }

        $methodDocblock = $reflectedMethod->getDocComment();

        if (! is_string($methodDocblock)) {
            return [];
        }

        $extractedTags = $this->phpDocTagsFinder->extractTagsFromStringedDocblock($methodDocblock, $tag);

        $classes = [];
        foreach ($extractedTags as $returnTag) {
            $classes[] = Reflection::expandClassName($returnTag, $reflectedMethod->getDeclaringClass());
        }

        return $classes;
    }
}
