<?php

declare(strict_types=1);

namespace Rector\PSR4\FileInfoAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassLike;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\ValueObject\Application\File;

final class FileInfoDeletionAnalyzer
{
    /**
     * @see https://regex101.com/r/8BdrI3/1
     * @var string
     */
    private const TESTING_PREFIX_REGEX = '#input_(.*?)_#';

    public function __construct(
        private ClassNaming $classNaming
    ) {
    }

    public function isClassLikeAndFileInfoMatch(File $file, ClassLike $classLike): bool
    {
        $className = $classLike->namespacedName->toString();
        $smartFileInfo = $file->getSmartFileInfo();

        $baseFileName = $this->clearNameFromTestingPrefix($smartFileInfo->getBasenameWithoutSuffix());
        $classShortName = $this->classNaming->getShortName($className);

        return $baseFileName === $classShortName;
    }

    public function clearNameFromTestingPrefix(string $name): string
    {
        return Strings::replace($name, self::TESTING_PREFIX_REGEX, '');
    }
}
