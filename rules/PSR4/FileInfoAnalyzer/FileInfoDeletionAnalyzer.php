<?php

declare (strict_types=1);
namespace Rector\PSR4\FileInfoAnalyzer;

use RectorPrefix20211109\Nette\Utils\Strings;
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
    /**
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    public function __construct(\Rector\CodingStyle\Naming\ClassNaming $classNaming)
    {
        $this->classNaming = $classNaming;
    }
    public function isClassLikeAndFileInfoMatch(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node\Stmt\ClassLike $classLike) : bool
    {
        $className = $classLike->namespacedName->toString();
        $smartFileInfo = $file->getSmartFileInfo();
        $baseFileName = $this->clearNameFromTestingPrefix($smartFileInfo->getBasenameWithoutSuffix());
        $classShortName = $this->classNaming->getShortName($className);
        return $baseFileName === $classShortName;
    }
    public function clearNameFromTestingPrefix(string $name) : string
    {
        return \RectorPrefix20211109\Nette\Utils\Strings::replace($name, self::TESTING_PREFIX_REGEX, '');
    }
}
