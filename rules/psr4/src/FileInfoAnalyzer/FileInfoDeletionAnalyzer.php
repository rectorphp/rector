<?php
declare(strict_types=1);

namespace Rector\PSR4\FileInfoAnalyzer;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\ClassLike;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class FileInfoDeletionAnalyzer
{
    /**
     * @see https://regex101.com/r/8BdrI3/1
     * @var string
     */
    private const TESTING_PREFIX_REGEX = '#input_(.*?)_#';

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(NodeNameResolver $nodeNameResolver, ClassNaming $classNaming)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->classNaming = $classNaming;
    }

    public function isClassLikeAndFileInfoMatch(ClassLike $classLike): bool
    {
        $className = $this->nodeNameResolver->getName($classLike);
        if ($className === null) {
            return false;
        }

        /** @var SmartFileInfo $smartFileInfo */
        $smartFileInfo = $classLike->getAttribute(SmartFileInfo::class);

        $baseFileName = $this->clearNameFromTestingPrefix($smartFileInfo->getBasenameWithoutSuffix());
        $classShortName = $this->classNaming->getShortName($className);

        return $baseFileName === $classShortName;
    }

    public function clearNameFromTestingPrefix(string $name): string
    {
        return Strings::replace($name, self::TESTING_PREFIX_REGEX, '');
    }
}
