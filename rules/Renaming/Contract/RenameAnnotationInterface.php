<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Renaming\Contract;

interface RenameAnnotationInterface
{
    public function getOldAnnotation() : string;
    public function getNewAnnotation() : string;
}
