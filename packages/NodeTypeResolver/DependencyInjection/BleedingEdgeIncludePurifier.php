<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\DependencyInjection;

use RectorPrefix202308\Nette\Utils\FileSystem;
use RectorPrefix202308\Nette\Utils\Strings;
/**
 * Prevents failing include of bleeding edge in of phpstan extensions.
 * @see https://github.com/rectorphp/rector/issues/2431
 *
 * Do not delete this. It's not tested here, but is needed to avoid this rare case happen. It's not possible to solve otherwise.
 * @see https://github.com/rectorphp/rector-src/commit/70fb9af2fdfa55db63c68c6dac6723fe55cec1a0
 * @eee https://github.com/rectorphp/rector/pull/2550
 *
 * @see \Rector\Tests\NodeTypeResolver\DependencyInjection\BleedingEdgeIncludePurifier\BleedingEdgeIncludePurifierTest
 */
final class BleedingEdgeIncludePurifier
{
    /**
     * @see https://regex101.com/r/CWADBe/2
     * @var string
     */
    private const BLEEDING_EDGE_REGEX = '#\\n\\s+-(.*?)bleedingEdge\\.neon[\'|"]?#';
    public function purifyConfigFile(string $filePath) : ?string
    {
        // must be neon file
        if (\substr_compare($filePath, '.neon', -\strlen('.neon')) !== 0) {
            return null;
        }
        $fileContents = FileSystem::read($filePath);
        // bleeding edge clean out, see https://github.com/rectorphp/rector/issues/2431
        $matches = Strings::match($fileContents, self::BLEEDING_EDGE_REGEX);
        if ($matches === null) {
            return null;
        }
        $temporaryFilePath = $this->createTemporaryFilePath($filePath);
        $clearedFileContents = Strings::replace($fileContents, self::BLEEDING_EDGE_REGEX);
        FileSystem::write($temporaryFilePath, $clearedFileContents);
        return $temporaryFilePath;
    }
    private function createTemporaryFilePath(string $filePath) : string
    {
        $fileDirectory = \dirname($filePath);
        $baseFileName = \pathinfo($filePath, \PATHINFO_BASENAME);
        $randomBytes = \random_bytes(10);
        $randomString = \bin2hex($randomBytes);
        return $fileDirectory . '/temp_' . $randomString . '_' . $baseFileName;
    }
}
