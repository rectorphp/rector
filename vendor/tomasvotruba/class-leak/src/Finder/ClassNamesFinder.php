<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\ClassLeak\Finder;

use Closure;
use RectorPrefix202609\TomasVotruba\ClassLeak\ClassNameResolver;
use RectorPrefix202609\TomasVotruba\ClassLeak\ValueObject\ClassNames;
use RectorPrefix202609\TomasVotruba\ClassLeak\ValueObject\FileWithClass;
final class ClassNamesFinder
{
    /**
     * @readonly
     */
    private ClassNameResolver $classNameResolver;
    public function __construct(ClassNameResolver $classNameResolver)
    {
        $this->classNameResolver = $classNameResolver;
    }
    /**
     * @param string[] $filePaths
     * @return FileWithClass[]
     */
    public function resolveClassNamesToCheck(array $filePaths, ?Closure $progressCallback): array
    {
        $filesWithClasses = [];
        foreach ($filePaths as $filePath) {
            ($nullsafeVariable1 = $progressCallback) ? $nullsafeVariable1->__invoke() : null;
            $classNames = $this->classNameResolver->resolveFromFilePath($filePath);
            if (!$classNames instanceof ClassNames) {
                continue;
            }
            $filesWithClasses[] = new FileWithClass($filePath, $classNames->getClassName(), $classNames->hasParentClassOrInterface(), $classNames->getAttributes());
        }
        return $filesWithClasses;
    }
}
