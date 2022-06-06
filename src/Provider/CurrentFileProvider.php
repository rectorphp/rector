<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Core\Provider;

use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
final class CurrentFileProvider
{
    /**
     * @var \Rector\Core\ValueObject\Application\File|null
     */
    private $file;
    public function setFile(File $file) : void
    {
        $this->file = $file;
    }
    public function getFile() : ?File
    {
        return $this->file;
    }
}
