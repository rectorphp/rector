<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Contract\Rector;

interface NonPhpRectorInterface extends RectorInterface
{
    public function refactorFileContent(string $fileContent) : string;
}
