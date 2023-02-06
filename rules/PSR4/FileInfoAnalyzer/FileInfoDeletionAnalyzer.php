<?php

declare (strict_types=1);
namespace Rector\PSR4\FileInfoAnalyzer;

use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use Rector\Core\ValueObject\Application\File;
final class FileInfoDeletionAnalyzer
{
    public function isClassLikeAndFileInfoMatch(File $file, ClassLike $classLike) : bool
    {
        if (!$classLike->name instanceof Identifier) {
            return \false;
        }
        $classShortName = $classLike->name->toString();
        $baseFilename = \pathinfo($file->getFilePath(), \PATHINFO_FILENAME);
        return $baseFilename === $classShortName;
    }
}
