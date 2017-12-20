<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\BetterReflection\Reflection\ReflectionClass;
use Rector\BetterReflection\Reflector\Exception\IdentifierNotFound;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Throwable;

final class SmartClassReflector
{
    /**
     * @var ClassReflectorFactory
     */
    private $classReflectorFactory;

    /**
     * @var ClassReflector
     */
    private $classReflector;

    /**
     * @var ReflectionClass[]
     */
    private $perClassNameClassReflections = [];

    /**
     * @var ParameterProvider
     */
    private $parameterProvider;

    /**
     * @var string[]
     */
    private $lastSource = [];

    public function __construct(ClassReflectorFactory $classReflectorFactory, ParameterProvider $parameterProvider)
    {
        $this->classReflectorFactory = $classReflectorFactory;
        $this->parameterProvider = $parameterProvider;
    }

    public function reflect(string $className): ?ReflectionClass
    {
        // invalid class types
        if (in_array($className, ['self', 'null', 'array', 'string', 'bool'])) {
            return null;
        }

        if (isset($this->perClassNameClassReflections[$className])) {
            return $this->perClassNameClassReflections[$className];
        }

        try {
            return $this->perClassNameClassReflections[$className] = $this->getClassReflector()->reflect($className);
        } catch (IdentifierNotFound $throwable) {
            return null;
        }
    }

    /**
     * @todo validate at least one is passed, or split to 2 methods?
     * @return string[]
     */
    public function getClassParents(?string $className = null, ?ClassLike $classLikeNode = null): array
    {
        // anonymous class
        if ($className === null) {
            if ($classLikeNode && property_exists($classLikeNode, 'extends')) {
                return [$classLikeNode->extends->toString()];
            }

            return [];
        }

        try {
            $classReflection = $this->reflect($className);

            return $classReflection->getParentClassNames();
        } catch (Throwable $throwable) {
            // intentionally empty
        }

        if ($classLikeNode) {
            return $this->resolveClassParentsFromNode($classLikeNode);
        }

        return [];
    }

    /**
     * @return string[]
     */
    private function resolveClassParentsFromNode(ClassLike $classLikeNode): array
    {
        if (! property_exists($classLikeNode, 'extends')) {
            return [];
        }

        if ($classLikeNode instanceof Class_) {
            return [$classLikeNode->extends->toString()];
        }

        if ($classLikeNode instanceof Interface_) {
            $types = [];
            foreach ($classLikeNode->extends as $interface) {
                $types[] = $interface->toString();
            }

            return $types;
        }
    }

    /**
     * Rebuilds when source changes, so it reflects current scope.
     * Useful mainly for tests.
     */
    private function getClassReflector(): ClassReflector
    {
        $currentSource = $this->parameterProvider->provideParameter('source');
        if ($this->lastSource === $currentSource) {
            return $this->classReflector;
        }

        if ($currentSource) {
            $this->lastSource = $currentSource;
            return $this->classReflector = $this->classReflectorFactory->createWithSource($currentSource);
        }

        return $this->classReflector = $this->classReflectorFactory->create();
    }
}
