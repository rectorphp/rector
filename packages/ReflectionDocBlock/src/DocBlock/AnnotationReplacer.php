<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\DocBlock;

use Nette\Utils\Strings;

final class AnnotationReplacer
{
    public function replaceOldByNew(string $content, string $oldAnnotation, string $newAnnotation): string
    {
        return Strings::replace($content, sprintf('#@%s#', $oldAnnotation), '@' . $newAnnotation);
    }
}
