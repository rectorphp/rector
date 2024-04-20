<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Reflection;

use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use Rector\FileSystem\FilePathHelper;
final class VendorLocationDetector
{
    /**
     * @readonly
     * @var \Rector\FileSystem\FilePathHelper
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
        return $this->detect($fileName);
    }
    public function detectFunctionReflection(FunctionReflection $functionReflection) : bool
    {
        $fileName = $functionReflection->getFileName();
        return $this->detect($fileName);
    }
    private function detect(?string $fileName = null) : bool
    {
        // probably internal
        if ($fileName === null) {
            return \false;
        }
        $normalizedFileName = $this->filePathHelper->normalizePathAndSchema($fileName);
        return \strpos($normalizedFileName, '/vendor/') !== \false;
    }
}
