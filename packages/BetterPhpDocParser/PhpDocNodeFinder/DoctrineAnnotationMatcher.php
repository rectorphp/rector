<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFinder;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;

final class DoctrineAnnotationMatcher
{
    public function matches(
        DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode,
        string $desiredClass
    ): bool {
        if ($doctrineAnnotationTagValueNode->hasClassName($desiredClass)) {
            return true;
        }

        // fnmatching class?
        if (! str_contains($desiredClass, '*')) {
            return false;
        }

        $identifierTypeNode = $doctrineAnnotationTagValueNode->identifierTypeNode;

        $className = trim($identifierTypeNode->name, '@');
        if ($this->isFnmatch($className, $desiredClass)) {
            return true;
        }

        // FQN check
        $resolvedClass = $identifierTypeNode->getAttribute(PhpDocAttributeKey::RESOLVED_CLASS);
        return is_string($resolvedClass) && $this->isFnmatch($resolvedClass, $desiredClass);
    }

    private function isFnmatch(string $currentValue, string $desiredValue): bool
    {
        return fnmatch($desiredValue, $currentValue, FNM_NOESCAPE);
    }
}
