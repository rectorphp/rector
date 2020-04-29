<?php

declare(strict_types=1);

namespace Rector\DoctrineAnnotationGenerated\PhpDocNode;

use Rector\DoctrineAnnotationGenerated\DataCollector\ResolvedConstantStaticCollector;

/**
 * @see https://github.com/rectorphp/rector/pull/3275/files
 */
final class ConstantReferenceIdentifierRestorer
{
    public function restoreObject(object $choice): void
    {
        // restore constant value back to original value
        $identifierToResolvedValues = ResolvedConstantStaticCollector::provide();
        if ($identifierToResolvedValues === []) {
            return;
        }

        $propertyNameToValues = get_object_vars($choice);
        foreach ($propertyNameToValues as $propertyName => $value) {
            $originalIdentifier = $this->matchIdentifierBasedOnResolverValue($identifierToResolvedValues, $value);
            if ($originalIdentifier !== null) {
                // restore value
                $choice->{$propertyName} = $originalIdentifier;
                continue;
            }

            // nested resolved value
            if (! is_array($value)) {
                continue;
            }

            foreach ($value as $key => $nestedValue) {
                $originalIdentifier = $this->matchIdentifierBasedOnResolverValue(
                    $identifierToResolvedValues,
                    $nestedValue
                );

                if ($originalIdentifier === null) {
                    continue;
                }

                // restore value
                $choice->{$propertyName}[$key] = $originalIdentifier;
            }
        }

        ResolvedConstantStaticCollector::clear();
    }

    /**
     * @return mixed|null
     */
    private function matchIdentifierBasedOnResolverValue(array $identifierToResolvedValues, $value)
    {
        foreach ($identifierToResolvedValues as $identifier => $resolvedValue) {
            if ($value !== $resolvedValue) {
                continue;
            }

            return $identifier;
        }

        return null;
    }
}
