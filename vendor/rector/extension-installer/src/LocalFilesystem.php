<?php

declare (strict_types=1);
namespace Rector\RectorInstaller;

use UnexpectedValueException;
final class LocalFilesystem implements \Rector\RectorInstaller\Filesystem
{
    public function isFile(string $pathToFile) : bool
    {
        return \is_file($pathToFile);
    }
    public function hashFile(string $pathToFile) : string
    {
        $fileHash = \md5_file($pathToFile);
        if ($fileHash === \false) {
            throw new \UnexpectedValueException(\sprintf('Could not hash file %s', $pathToFile));
        }
        return $fileHash;
    }
    public function writeFile(string $pathToFile, string $contents) : void
    {
        $bytes = \file_put_contents($pathToFile, $contents);
        if ($bytes === \false) {
            throw new \UnexpectedValueException(\sprintf('Could not write contents to file %s', $pathToFile));
        }
    }
    public function hashEquals(string $hash, string $content) : bool
    {
        return $hash === \md5($content);
    }
}
