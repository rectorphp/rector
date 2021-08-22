<?php

declare (strict_types=1);
namespace Rector\Nette\FileProcessor;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Nette\Contract\Rector\LatteRectorInterface;
final class LatteFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var \Rector\Nette\Contract\Rector\LatteRectorInterface[]
     */
    private $latteRectors;
    /**
     * @param LatteRectorInterface[] $latteRectors
     */
    public function __construct(array $latteRectors)
    {
        $this->latteRectors = $latteRectors;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function process($file, $configuration) : void
    {
        $fileContent = $file->getFileContent();
        foreach ($this->latteRectors as $latteRector) {
            $fileContent = $latteRector->changeContent($fileContent);
        }
        $file->changeFileContent($fileContent);
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function supports($file, $configuration) : bool
    {
        $fileInfo = $file->getSmartFileInfo();
        return $fileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return ['latte'];
    }
}
