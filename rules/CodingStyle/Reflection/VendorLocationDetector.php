<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Reflection;

use PHPStan\Reflection\MethodReflection;
use Rector\Core\FileSystem\FilePathHelper;
final class VendorLocationDetector
{
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    public function __construct(FilePathHelper $filePathHelper)
    {
        $this->filePathHelper = $filePathHelper;
    }
    public function detectMethodReflection(MethodReflection $methodReflection) : bool
    {
        $declaringClassReflection = $methodReflection->getDeclaringClass();
        $fileName = $declaringClassReflection->getFileName();
        // probably internal
        if ($fileName === null) {
            return \false;
        }
        $normalizedFileName = $this->filePathHelper->normalizePathAndSchema($fileName);
        return \strpos($normalizedFileName, '/vendor/') !== \false;
    }
}
