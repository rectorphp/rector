<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Reflection;

use PHPStan\Reflection\MethodReflection;
use Symplify\SmartFileSystem\Normalizer\PathNormalizer;

final class VendorLocationDetector
{
    public function __construct(
        private readonly PathNormalizer $pathNormalizer,
    ) {
    }

    public function detectMethodReflection(MethodReflection $methodReflection): bool
    {
        $declaringClassReflection = $methodReflection->getDeclaringClass();
        $fileName = $declaringClassReflection->getFileName();

        // probably internal
        if ($fileName === null) {
            return false;
        }

        $normalizedFileName = $this->pathNormalizer->normalizePath($fileName);
        return str_contains($normalizedFileName, '/vendor/');
    }
}
