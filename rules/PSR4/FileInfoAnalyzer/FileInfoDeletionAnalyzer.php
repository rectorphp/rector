<?php

declare(strict_types=1);

namespace Rector\PSR4\FileInfoAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassLike;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeNameResolver\NodeNameResolver;

final class FileInfoDeletionAnalyzer
{
    /**
     * @see https://regex101.com/r/8BdrI3/1
     * @var string
     */
    private const TESTING_PREFIX_REGEX = '#input_(.*?)_#';

    public function __construct(
        private ClassNaming $classNaming,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function isClassLikeAndFileInfoMatch(File $file, ClassLike $classLike): bool
    {
        $className = (string) $this->nodeNameResolver->getName($classLike);
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
