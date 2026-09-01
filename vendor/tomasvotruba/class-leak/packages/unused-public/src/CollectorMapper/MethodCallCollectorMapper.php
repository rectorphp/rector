<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic\CollectorMapper;

use RectorPrefix202609\TomasVotruba\UnusedPublic\Enum\ReferenceMarker;
use RectorPrefix202609\TomasVotruba\UnusedPublic\Utils\Arrays;
use RectorPrefix202609\TomasVotruba\UnusedPublic\ValueObject\LocalAndExternalMethodCallReferences;
final class MethodCallCollectorMapper
{
    /**
     * @param array<array<string, mixed[]>> $nestedReferencesByFiles
     * @return string[]
     */
    public function mapToMethodCallReferences(array $nestedReferencesByFiles): array
    {
        $methodCallReferences = $this->mergeAndFlatten($nestedReferencesByFiles);
        // remove ReferenceMaker::LOCAL prefix
        return array_map(static function (string $methodCallReference): string {
            if (strncmp($methodCallReference, ReferenceMarker::LOCAL, strlen(ReferenceMarker::LOCAL)) === 0) {
                return (string) substr($methodCallReference, strlen(ReferenceMarker::LOCAL));
            }
            return $methodCallReference;
        }, $methodCallReferences);
    }
    /**
     * @param array<array<string, mixed[]>> $nestedReferencesByFiles
     */
    public function mapToLocalAndExternal(array $nestedReferencesByFiles): LocalAndExternalMethodCallReferences
    {
        $methodCallReferences = $this->mergeAndFlatten($nestedReferencesByFiles);
        $localMethodCallReferences = [];
        $externalMethodCallReferences = [];
        foreach ($methodCallReferences as $methodCallReference) {
            if (strncmp($methodCallReference, ReferenceMarker::LOCAL, strlen(ReferenceMarker::LOCAL)) === 0) {
                $localMethodCallReferences[] = (string) substr($methodCallReference, strlen(ReferenceMarker::LOCAL));
            } else {
                $externalMethodCallReferences[] = $methodCallReference;
            }
        }
        return new LocalAndExternalMethodCallReferences($localMethodCallReferences, $externalMethodCallReferences);
    }
    /**
     * @param array<array<string, mixed[]>> $nestedReferencesByFiles
     * @return string[]
     */
    private function mergeAndFlatten(array $nestedReferencesByFiles): array
    {
        $flattenReferences = [];
        foreach ($nestedReferencesByFiles as $nestedReferences) {
            $flattenReferences = array_merge($flattenReferences, Arrays::flatten($nestedReferences));
        }
        return $flattenReferences;
    }
}
