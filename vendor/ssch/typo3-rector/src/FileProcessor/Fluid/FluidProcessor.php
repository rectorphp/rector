<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Fluid;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Ssch\TYPO3Rector\Contract\FileProcessor\Fluid\Rector\FluidRectorInterface;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Fluid\FluidProcessorTest
 */
final class FluidProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var mixed[]
     */
    private $fluidRectors;
    /**
     * @param FluidRectorInterface[] $fluidRectors
     */
    public function __construct(array $fluidRectors)
    {
        $this->fluidRectors = $fluidRectors;
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return \in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions(), \true);
    }
    /**
     * @param File[] $files
     */
    public function process(array $files) : void
    {
        if ([] === $this->fluidRectors) {
            return;
        }
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }
    public function getSupportedFileExtensions() : array
    {
        return ['html', 'xml', 'txt'];
    }
    private function processFile(\Rector\Core\ValueObject\Application\File $file) : void
    {
        foreach ($this->fluidRectors as $fluidRector) {
            $fluidRector->transform($file);
        }
    }
}
