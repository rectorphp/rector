<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Interface_;
use Rector\BetterReflection\Reflection\ReflectionClass;
use Rector\BetterReflection\Reflector\Exception\IdentifierNotFound;
use Rector\FileSystem\CurrentFileProvider;
use SplFileInfo;
use Throwable;
use TypeError;

final class SmartClassReflector
{
    /**
     * @var ClassReflectorFactory
     */
    private $classReflectorFactory;

    /**
     * @var CurrentFileProvider
     */
    private $currentFileProvider;

    /**
     * @var ClassReflector
     */
    private $currentClassReflector;

    /**
     * @var SplFileInfo
     */
    private $classReflectorActiveFile;

    public function __construct(ClassReflectorFactory $classReflectorFactory, CurrentFileProvider $currentFileProvider)
    {
        $this->classReflectorFactory = $classReflectorFactory;
        $this->currentFileProvider = $currentFileProvider;
    }

    public function reflect(string $className): ?ReflectionClass
    {
        try {
            if ($this->shouldCreateNewClassReflector()) {
                $this->createNewClassReflector();
            }

            return $this->currentClassReflector->reflect($className);
        } catch (IdentifierNotFound|TypeError $throwable) {
            return null;
        }
    }

    /**
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

        $classReflection = $this->reflect($className);

        try {
            return $classReflection->getParentClassNames();
        } catch (Throwable $throwable) {
            if ($classLikeNode) {
                return $this->resolveClassParentsFromNode($classLikeNode);
            }
        }

        return [];
    }

    private function createNewClassReflector(): void
    {
        $currentFile = $this->currentFileProvider->getCurrentFile();

        if ($currentFile === null) {
            $this->currentClassReflector = $this->classReflectorFactory->create();
        } else {
            $this->currentClassReflector = $this->classReflectorFactory->createWithFile($currentFile);
            $this->classReflectorActiveFile = $currentFile;
        }
    }

    private function shouldCreateNewClassReflector(): bool
    {
        if ($this->currentClassReflector === null) {
            return true;
        }

        return $this->classReflectorActiveFile !== $this->currentFileProvider->getCurrentFile();
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
}
