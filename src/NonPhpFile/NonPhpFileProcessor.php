<?php

declare (strict_types=1);
namespace Rector\Core\NonPhpFile;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Contract\Rector\NonPhpRectorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
final class NonPhpFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var \Rector\Core\Contract\Rector\NonPhpRectorInterface[]
     */
    private $nonPhpRectors;
    /**
     * @param NonPhpRectorInterface[] $nonPhpRectors
     */
    public function __construct(array $nonPhpRectors)
    {
        $this->nonPhpRectors = $nonPhpRectors;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function process($file, $configuration) : void
    {
        foreach ($this->nonPhpRectors as $nonPhpRector) {
            $newFileContent = $nonPhpRector->refactorFileContent($file->getFileContent());
            $file->changeFileContent($newFileContent);
        }
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function supports($file, $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        // early assign to variable for increase performance
        // @see https://3v4l.org/FM3vY#focus=8.0.7 vs https://3v4l.org/JZW7b#focus=8.0.7
        $pathname = $smartFileInfo->getPathname();
        // bug in path extension
        foreach ($this->getSupportedFileExtensions() as $fileExtension) {
            if (\substr_compare($pathname, '.' . $fileExtension, -\strlen('.' . $fileExtension)) === 0) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return \Rector\Core\ValueObject\StaticNonPhpFileSuffixes::SUFFIXES;
    }
}
