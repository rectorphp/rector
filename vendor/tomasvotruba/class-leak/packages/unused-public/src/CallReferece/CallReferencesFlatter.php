<?php

declare (strict_types=1);
namespace RectorPrefix202608\TomasVotruba\UnusedPublic\CallReferece;

use RectorPrefix202608\TomasVotruba\UnusedPublic\Enum\ReferenceMarker;
use RectorPrefix202608\TomasVotruba\UnusedPublic\ValueObject\MethodCallReference;
final class CallReferencesFlatter
{
    /**
     * @param MethodCallReference[] $classMethodCallReferences
     * @return string[]
     */
    public function flatten(array $classMethodCallReferences): array
    {
        $classMethodReferences = [];
        foreach ($classMethodCallReferences as $classMethodCallReference) {
            $className = $classMethodCallReference->getClass();
            $methodName = $classMethodCallReference->getMethod();
            $classMethodReference = $className . '::' . $methodName;
            if ($classMethodCallReference->isLocal()) {
                $classMethodReference = ReferenceMarker::LOCAL . $classMethodReference;
            }
            $classMethodReferences[] = $classMethodReference;
        }
        return $classMethodReferences;
    }
}
