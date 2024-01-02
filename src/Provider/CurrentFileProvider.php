<?php

declare (strict_types=1);
namespace Rector\Provider;

use Rector\ValueObject\Application\File;
final class CurrentFileProvider
{
    /**
     * @var \Rector\ValueObject\Application\File|null
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
