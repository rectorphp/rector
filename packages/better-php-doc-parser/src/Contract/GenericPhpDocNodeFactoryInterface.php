<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Contract;

interface GenericPhpDocNodeFactoryInterface extends PhpDocNodeFactoryInterface
{
    /**
     * @return string[]
     */
    public function getTagValueNodeClassesToAnnotationClasses(): array;
}
