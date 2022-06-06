<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Contract\Application;

use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
interface FileDecoratorInterface
{
    /**
     * @param File[] $files
     */
    public function decorate(array $files) : void;
}
