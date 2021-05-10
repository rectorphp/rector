<?php

declare(strict_types=1);

namespace Rector\Core\Reflection;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Parser;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ClassReflectionToAstResolver
{
    public function __construct(
        private Parser $parser,
        private SmartFileSystem $smartFileSystem,
        private BetterNodeFinder $betterNodeFinder,
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function getClassFromObjectType(ObjectType $objectType): ?Class_
    {
        if (! $this->reflectionProvider->hasClass($objectType->getClassName())) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($objectType->getClassName());
        return $this->getClass($classReflection, $objectType->getClassName());
    }

    private function getClass(ClassReflection $classReflection, string $className): ?Class_
    {
        if ($classReflection->isBuiltin()) {
            return null;
        }

        /** @var string $fileName */
        $fileName = $classReflection->getFileName();

        /** @var Node[] $contentNodes */
        $contentNodes = $this->parser->parse($this->smartFileSystem->readFile($fileName));

        /** @var Class_[] $classes */
        $classes = $this->betterNodeFinder->findInstanceOf($contentNodes, Class_::class);
        if ($classes === []) {
            return null;
        }

        $reflectionClassName = $classReflection->getName();
        foreach ($classes as $class) {
            if ($reflectionClassName === $className) {
                return $class;
            }
        }

        return null;
    }
}
