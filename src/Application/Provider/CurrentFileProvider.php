<?php

declare (strict_types=1);
namespace Rector\Application\Provider;

use Rector\ValueObject\Application\File;
/**
 * @internal Avoid this services if possible, pass File value object or file path directly
 */
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
