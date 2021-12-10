<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Fluid;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Ssch\TYPO3Rector\Contract\FileProcessor\Fluid\Rector\FluidRectorInterface;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Fluid\FluidProcessorTest
 */
final class FluidFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var \Ssch\TYPO3Rector\Contract\FileProcessor\Fluid\Rector\FluidRectorInterface[]
     */
    private $fluidRectors;
    /**
     * @param FluidRectorInterface[] $fluidRectors
     */
    public function __construct(array $fluidRectors)
    {
        $this->fluidRectors = $fluidRectors;
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return \in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions(), \true);
    }
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        if ([] === $this->fluidRectors) {
            return;
        }
        foreach ($this->fluidRectors as $fluidRector) {
            $fluidRector->transform($file);
        }
    }
    public function getSupportedFileExtensions() : array
    {
        return ['html', 'xml', 'txt'];
    }
}
