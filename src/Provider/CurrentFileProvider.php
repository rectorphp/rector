<?php

declare (strict_types=1);
namespace Rector\Core\Provider;

use Rector\Core\ValueObject\Application\File;
final class CurrentFileProvider
{
    /**
     * @var \Rector\Core\ValueObject\Application\File|null
     */
    private $file;
    public function setFile(\Rector\Core\ValueObject\Application\File $file) : void
    {
        $this->file = $file;
    }
    public function getFile() : ?\Rector\Core\ValueObject\Application\File
    {
        return $this->file;
    }
}
