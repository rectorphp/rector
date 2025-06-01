<?php

declare (strict_types=1);
namespace Rector\Doctrine\NodeAnalyzer;

use RectorPrefix202506\Nette\Utils\FileSystem;
use RectorPrefix202506\Nette\Utils\Strings;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Exception\ShouldNotHappenException;
final class RepositoryClassResolver
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string
     */
    private const QUOTED_REPOSITORY_CLASS_REGEX = '#repositoryClass=\\"(?<repositoryClass>.*?)\\"#';
    /**
     * @var string
     */
    private const USE_REPOSITORY_REGEX = '#use (?<repositoryClass>.*?Repository);#';
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function resolveFromEntityClass(string $entityClassName) : ?string
    {
        if (!$this->reflectionProvider->hasClass($entityClassName)) {
            throw new ShouldNotHappenException();
        }
        $classReflection = $this->reflectionProvider->getClass($entityClassName);
        $entityClassFileName = $classReflection->getFileName();
        if ($entityClassFileName === null) {
            return null;
        }
        $entityFileContents = FileSystem::read($entityClassFileName);
        $repositoryClass = null;
        // match repositoryClass="..." in entity
        $match = Strings::match($entityFileContents, self::QUOTED_REPOSITORY_CLASS_REGEX);
        if (!isset($match['repositoryClass'])) {
            // try fallback to repository ::class + use import
            $repositoryUseMatch = Strings::match($entityFileContents, self::USE_REPOSITORY_REGEX);
            if (isset($repositoryUseMatch['repositoryClass'])) {
                $repositoryClass = $repositoryUseMatch['repositoryClass'];
            }
        } else {
            $repositoryClass = $match['repositoryClass'];
        }
        if ($repositoryClass === null) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($repositoryClass)) {
            throw new ShouldNotHappenException(\sprintf('Repository class "%s" for entity "%s" does not exist', $repositoryClass, $entityClassName));
        }
        return $repositoryClass;
    }
}
