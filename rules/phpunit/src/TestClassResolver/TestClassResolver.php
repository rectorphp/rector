<?php

declare(strict_types=1);

namespace Rector\PHPUnit\TestClassResolver;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use Rector\NodeNameResolver\NodeNameResolver;

final class TestClassResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var PHPUnitTestCaseClassesProvider
     */
    private $phpUnitTestCaseClassesProvider;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        PHPUnitTestCaseClassesProvider $phpUnitTestCaseClassesProvider
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->phpUnitTestCaseClassesProvider = $phpUnitTestCaseClassesProvider;
    }

    public function resolveFromClassName(string $className): ?string
    {
        // fallback for unit tests that only have extra "Test" suffix
        if (class_exists($className . 'Test')) {
            return $className . 'Test';
        }

        $shortClassName = Strings::after($className, '\\', -1);
        $testShortClassName = $shortClassName . 'Test';

        $phpUnitTestCaseClasses = $this->phpUnitTestCaseClassesProvider->provide();
        foreach ($phpUnitTestCaseClasses as $declaredClass) {
            if (Strings::endsWith($declaredClass, '\\' . $testShortClassName)) {
                return $declaredClass;
            }
        }

        return null;
    }

    public function resolveFromClass(Class_ $class): ?string
    {
        $className = $this->nodeNameResolver->getName($class);
        if ($className === null) {
            return null;
        }

        return $this->resolveFromClassName($className);
    }
}
