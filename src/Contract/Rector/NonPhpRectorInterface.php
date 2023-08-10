<?php

declare (strict_types=1);
namespace Rector\Core\Contract\Rector;

/**
 * @deprecated Rector should never handle anything outside PHP files, as oustide it's scope - use custom tool instead.
 */
interface NonPhpRectorInterface extends \Rector\Core\Contract\Rector\RectorInterface
{
    public function refactorFileContent(string $fileContent) : string;
}
