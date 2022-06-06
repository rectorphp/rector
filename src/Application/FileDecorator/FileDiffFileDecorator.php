<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Application\FileDecorator;

use RectorPrefix20220606\Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use RectorPrefix20220606\Rector\Core\Contract\Application\FileDecoratorInterface;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
final class FileDiffFileDecorator implements FileDecoratorInterface
{
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    public function __construct(FileDiffFactory $fileDiffFactory)
    {
        $this->fileDiffFactory = $fileDiffFactory;
    }
    /**
     * @param File[] $files
     */
    public function decorate(array $files) : void
    {
        foreach ($files as $file) {
            if (!$file->hasChanged()) {
                continue;
            }
            $fileDiff = $this->fileDiffFactory->createFileDiff($file, $file->getOriginalFileContent(), $file->getFileContent());
            $file->setFileDiff($fileDiff);
        }
    }
}
