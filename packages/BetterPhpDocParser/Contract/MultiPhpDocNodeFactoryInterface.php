<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

/**
 * @deprecated Use DoctrineAnnotation parser instead
 */
interface MultiPhpDocNodeFactoryInterface
{
    /**
     * @return array<string, class-string>
     */
    public function getTagValueNodeClassesToAnnotationClasses(): array;
}
