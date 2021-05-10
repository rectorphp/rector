<?php

declare(strict_types=1);

namespace Rector\Core\Application\FileDecorator;

use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Core\Contract\Application\FileDecoratorInterface;
use Rector\Core\ValueObject\Application\File;

final class FileDiffFileDecorator implements FileDecoratorInterface
{
    public function __construct(
        private FileDiffFactory $fileDiffFactory
    ) {
    }

    /**
     * @param File[] $files
     */
    public function decorate(array $files): void
    {
        foreach ($files as $file) {
            if (! $file->hasChanged()) {
                continue;
            }

            $fileDiff = $this->fileDiffFactory->createFileDiff(
                $file,
                $file->getOriginalFileContent(),
                $file->getFileContent()
            );

            $file->setFileDiff($fileDiff);
        }
    }
}
