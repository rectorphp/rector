<?php

declare (strict_types=1);
namespace Rector\RectorInstaller;

use UnexpectedValueException;
final class LocalFilesystem implements \Rector\RectorInstaller\Filesystem
{
    /**
     * @param string $pathToFile
     */
    public function isFile($pathToFile) : bool
    {
        return \is_file($pathToFile);
    }
    /**
     * @param string $pathToFile
     */
    public function hashFile($pathToFile) : string
    {
        $fileHash = \md5_file($pathToFile);
        if ($fileHash === \false) {
            throw new \UnexpectedValueException(\sprintf('Could not hash file %s', $pathToFile));
        }
        return $fileHash;
    }
    /**
     * @param string $pathToFile
     * @param string $contents
     */
    public function writeFile($pathToFile, $contents) : void
    {
        $bytes = \file_put_contents($pathToFile, $contents);
        if ($bytes === \false) {
            throw new \UnexpectedValueException(\sprintf('Could not write contents to file %s', $pathToFile));
        }
    }
    /**
     * @param string $hash
     * @param string $content
     */
    public function hashEquals($hash, $content) : bool
    {
        return $hash === \md5($content);
    }
}
