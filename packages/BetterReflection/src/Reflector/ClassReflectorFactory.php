<?php declare(strict_types=1);

namespace Rector\BetterReflection\Reflector;

use Rector\BetterReflection\SourceLocator\SourceLocatorFactory;
use Roave\BetterReflection\Reflector\ClassReflector;

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

    /**
     * @param string[] $source Files or directories
     */
    public function createWithSource(array $source): ClassReflector
    {
        $sourceLocator = $this->sourceLocatorFactory->createWithSource($source);

        return new ClassReflector($sourceLocator);
    }
}
