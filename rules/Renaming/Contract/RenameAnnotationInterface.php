<?php

declare (strict_types=1);
namespace Rector\Renaming\Contract;

/**
 * @api used in deprecated RenameAnnotationRector configs
 */
interface RenameAnnotationInterface
{
    public function getOldAnnotation(): string;
    public function getNewAnnotation(): string;
}
