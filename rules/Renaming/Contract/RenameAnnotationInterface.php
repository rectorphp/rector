<?php

declare (strict_types=1);
namespace Rector\Renaming\Contract;

interface RenameAnnotationInterface
{
    public function getOldAnnotation() : string;
    public function getNewAnnotation() : string;
}
