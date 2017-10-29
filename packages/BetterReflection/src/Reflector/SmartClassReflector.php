<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

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
     * @var SmartClassReflector
     */
    private $currentSmartClassReflector;

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

            return $this->currentSmartClassReflector->reflect($className);
        } catch (IdentifierNotFound|TypeError $throwable) {
            return null;
        }
    }

    /**
     * @return string[]
     */
    public function getClassParents(string $className): array
    {
        $classReflection = $this->reflect($className);

        try {
            return $classReflection->getParentClassNames();
        } catch (Throwable $throwable) {
            return [];
        }
    }

    private function createNewClassReflector(): void
    {
        $currentFile = $this->currentFileProvider->getCurrentFile();

        if ($currentFile === null) {
            $this->currentSmartClassReflector = $this->classReflectorFactory->create();
        } else {
            $this->currentSmartClassReflector = $this->classReflectorFactory->createWithFile($currentFile);
            $this->classReflectorActiveFile = $currentFile;
        }
    }

    private function shouldCreateNewClassReflector(): bool
    {
        if ($this->currentSmartClassReflector === null) {
            return true;
        }

        return $this->classReflectorActiveFile !== $this->currentFileProvider->getCurrentFile();
    }
}
