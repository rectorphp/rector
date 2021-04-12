<?php
declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Renaming\Configuration\MethodCallRenameCollector;

class NeonMethodCallRenamer implements FileProcessorInterface
{
    /**
     * @var MethodCallRenameCollector
     */
    private $methodCallRenameCollector;

    public function __construct(MethodCallRenameCollector $methodCallRenameCollector)
    {
        $this->methodCallRenameCollector = $methodCallRenameCollector;
    }

    /**
     * @param File[] $files
     */
    public function process(array $files): void
    {
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }

    private function processFile(File $file): void
    {
        $content = $file->getFileContent();
        foreach ($this->methodCallRenameCollector->getMethodCallRenames() as $methodCallRename) {
            $oldObjectType = $methodCallRename->getOldObjectType();
            $objectClassName = $oldObjectType->getClassName();
            $className = str_replace('\\', '\\\\', $objectClassName);

            $oldMethodName = $methodCallRename->getOldMethod();
            $newMethodName = $methodCallRename->getNewMethod();

            $pattern = '/\n(.*?)(class|factory): ' . $className . '(\n|\((.*?)\)\n)\1setup:(.*?)- ' . $oldMethodName . '\(/s';
            while (preg_match($pattern, $content, $matches)) {
                $replacedMatch = str_replace($oldMethodName . '(', $newMethodName . '(', $matches[0]);
                $content = str_replace($matches[0], $replacedMatch, $content);
            }
        }
        $file->changeFileContent($content);
    }

    public function supports(File $file): bool
    {
        $fileInfo = $file->getSmartFileInfo();
        return $fileInfo->hasSuffixes($this->getSupportedFileExtensions());
    }

    public function getSupportedFileExtensions(): array
    {
        return ['neon'];
    }
}
