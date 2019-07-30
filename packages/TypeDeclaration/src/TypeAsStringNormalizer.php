<?php declare(strict_types=1);

namespace Rector\TypeDeclaration;

use PHPStan\Type\IntersectionType;
use PHPStan\Type\Type;

final class TypeAsStringNormalizer
{
    /**
     * @param string[] $typesAsStrings
     */
    public function normalizeTypesAsStringsToString(array $typesAsStrings, Type $staticType): string
    {
        foreach ($typesAsStrings as $i => $typesAsString) {
            $typesAsStrings[$i] = $this->removePreSlash($typesAsString);
        }

        $separateChar = $staticType instanceof IntersectionType ? '&' : '|';

        return implode($separateChar, $typesAsStrings);
    }

    private function removePreSlash(string $content): string
    {
        return ltrim($content, '\\');
    }
}
