<?php

declare (strict_types=1);
namespace Rector\FileSystemRector\Contract;

interface AddedFileInterface
{
    public function getFilePath() : string;
}
