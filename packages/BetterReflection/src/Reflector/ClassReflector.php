<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use Rector\FileSystem\CurrentFileProvider;
use Roave\BetterReflection\Reflection\ReflectionClass;
use SplFileInfo;

final class ClassReflector
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
    private $classReflector;

    /**
     * @var SplFileInfo
     */
    private $classReflectorActiveFile;

    public function __construct(
        ClassReflectorFactory $classReflectorFactory,
        CurrentFileProvider $currentFileProvider
    ) {
        $this->classReflectorFactory = $classReflectorFactory;
        $this->currentFileProvider = $currentFileProvider;
    }

    public function reflect(string $className): ReflectionClass
    {
        if ($this->shouldCreateNewClassReflector()) {
            $this->createNewClassReflector();
        }

        return $this->classReflector->reflect($className);
    }

    private function createNewClassReflector(): void
    {
        $currentFile = $this->currentFileProvider->getCurrentFile();

        if ($currentFile === null) {
            $this->classReflector = $this->classReflectorFactory->create();
        } else {
            $this->classReflector = $this->classReflectorFactory->createWithFile($currentFile);
            $this->classReflectorActiveFile = $currentFile;
        }
    }

    private function shouldCreateNewClassReflector(): bool
    {
        if ($this->classReflector === null) {
            return true;
        }

        return $this->classReflectorActiveFile !== $this->currentFileProvider->getCurrentFile();
    }
}
