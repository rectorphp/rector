<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Rector;

interface NonPhpRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface
{
    /**
     * @param string $fileContent
     */
    public function refactorFileContent($fileContent) : string;
}
