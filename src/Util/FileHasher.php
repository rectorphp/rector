<?php

declare (strict_types=1);
namespace Rector\Core\Util;

use Rector\Core\Exception\ShouldNotHappenException;
/**
 * @see \Rector\Core\Tests\Util\FileHasherTest
 */
final class FileHasher
{
    /**
     * cryptographic insecure hasing of a string
     */
    public function hash(string $string) : string
    {
        return \hash($this->getAlgo(), $string);
    }
    /**
     * cryptographic insecure hasing of files
     *
     * @param string[] $files
     */
    public function hashFiles(array $files) : string
    {
        $configHash = '';
        $algo = $this->getAlgo();
        foreach ($files as $file) {
            $hash = \hash_file($algo, $file);
            if ($hash === \false) {
                throw new ShouldNotHappenException(\sprintf('File %s is not readable', $file));
            }
            $configHash .= $hash;
        }
        return $configHash;
    }
    private function getAlgo() : string
    {
        //see https://php.watch/articles/php-hash-benchmark
        if (\PHP_VERSION_ID >= 80100) {
            // if xxh128 is available use it, as it is way faster
            return 'xxh128';
        }
        return 'md4';
    }
}
