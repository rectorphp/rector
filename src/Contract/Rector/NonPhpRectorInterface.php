<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Rector;

interface NonPhpRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface
{
    public function refactorFileContent(string $fileContent) : string;
}
