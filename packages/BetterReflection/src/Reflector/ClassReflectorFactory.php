<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use Rector\BetterReflection\SourceLocator\SourceLocatorFactory;
use Rector\BetterReflection\Reflector\ClassReflector;
use SplFileInfo;

final class ClassReflectorFactory
{
    /**
     * @var SourceLocatorFactory
     */
    private $sourceLocatorFactory;

    public function __construct(SourceLocatorFactory $sourceLocatorFactory)
    {
        $this->sourceLocatorFactory = $sourceLocatorFactory;
    }

    public function create(): ClassReflector
    {
        return new ClassReflector($this->sourceLocatorFactory->create());
    }

    public function createWithFile(SplFileInfo $fileInfo): ClassReflector
    {
        $sourceLocator = $this->sourceLocatorFactory->createWithFile($fileInfo);

        return new ClassReflector($sourceLocator);
    }
}
