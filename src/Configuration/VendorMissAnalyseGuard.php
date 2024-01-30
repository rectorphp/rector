<?php

declare (strict_types=1);
namespace Rector\Configuration;

use Rector\Configuration\Parameter\SimpleParameterProvider;
use Rector\Skipper\FileSystem\PathNormalizer;
final class VendorMissAnalyseGuard
{
    /**
     * @param string[] $filePaths
     */
    public function isVendorAnalyzed(array $filePaths) : bool
    {
        if ($this->hasDowngradeSets()) {
            return \false;
        }
        return $this->containsVendorPath($filePaths);
    }
    private function hasDowngradeSets() : bool
    {
        $registeredRectorSets = SimpleParameterProvider::provideArrayParameter(\Rector\Configuration\Option::REGISTERED_RECTOR_SETS);
        foreach ($registeredRectorSets as $registeredRectorSet) {
            if (\strpos((string) $registeredRectorSet, 'downgrade-') !== \false) {
                return \true;
            }
        }
        return \false;
    }
    /**
     * @param string[] $filePaths
     */
    private function containsVendorPath(array $filePaths) : bool
    {
        foreach ($filePaths as $filePath) {
            if (\strpos(PathNormalizer::normalize($filePath), '/vendor/') !== \false) {
                return \true;
            }
        }
        return \false;
    }
}
