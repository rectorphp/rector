<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use Rector\BetterReflection\Reflection\ReflectionClass;
use Rector\BetterReflection\Reflector\Exception\IdentifierNotFound;
use Rector\FileSystem\CurrentFileProvider;
use SplFileInfo;
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
    private $smartClassReflector;

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

            return $this->smartClassReflector->reflect($className);
        } catch (IdentifierNotFound $identifierNotFoundException) {
            return null;

        } catch (TypeError $typeError) {
            return null;
        }
    }

    private function createNewClassReflector(): void
    {
        $currentFile = $this->currentFileProvider->getCurrentFile();

        if ($currentFile === null) {
            $this->smartClassReflector = $this->classReflectorFactory->create();
        } else {
            $this->smartClassReflector = $this->classReflectorFactory->createWithFile($currentFile);
            $this->classReflectorActiveFile = $currentFile;
        }
    }

    private function shouldCreateNewClassReflector(): bool
    {
        if ($this->smartClassReflector === null) {
            return true;
        }

        return $this->classReflectorActiveFile !== $this->currentFileProvider->getCurrentFile();
    }
}
