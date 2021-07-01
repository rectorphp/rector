<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocNodeFinder;

use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
final class DoctrineAnnotationMatcher
{
    public function matches(\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode $doctrineAnnotationTagValueNode, string $desiredClass) : bool
    {
        if ($doctrineAnnotationTagValueNode->hasClassName($desiredClass)) {
            return \true;
        }
        // fnmatching class?
        if (\strpos($desiredClass, '*') === \false) {
            return \false;
        }
        $identifierTypeNode = $doctrineAnnotationTagValueNode->identifierTypeNode;
        if ($this->isFnmatch($identifierTypeNode->name, $desiredClass)) {
            return \true;
        }
        // FQN check
        $resolvedClass = $identifierTypeNode->getAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::RESOLVED_CLASS);
        return \is_string($resolvedClass) && $this->isFnmatch($resolvedClass, $desiredClass);
    }
    private function isFnmatch(string $currentValue, string $desiredValue) : bool
    {
        return \fnmatch($desiredValue, $currentValue, \FNM_NOESCAPE);
    }
}
