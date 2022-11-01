<?php

declare (strict_types=1);
namespace Rector\PSR4\FileInfoAnalyzer;

use RectorPrefix202211\Nette\Utils\Strings;
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
    /**
     * @readonly
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(ClassNaming $classNaming, NodeNameResolver $nodeNameResolver)
    {
        $this->classNaming = $classNaming;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function isClassLikeAndFileInfoMatch(File $file, ClassLike $classLike) : bool
    {
        $className = (string) $this->nodeNameResolver->getName($classLike);
        $filePath = $file->getFilePath();
        $basename = \pathinfo($filePath, \PATHINFO_BASENAME);
        $baseFileName = $this->clearNameFromTestingPrefix($basename);
        $classShortName = $this->classNaming->getShortName($className);
        return $baseFileName === $classShortName;
    }
    public function clearNameFromTestingPrefix(string $name) : string
    {
        return Strings::replace($name, self::TESTING_PREFIX_REGEX, '');
    }
}
