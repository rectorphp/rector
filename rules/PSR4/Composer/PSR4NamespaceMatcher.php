<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\PSR4\Composer;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Rector\Tests\PSR4\Composer\PSR4NamespaceMatcherTest
 */
final class PSR4NamespaceMatcher implements PSR4AutoloadNamespaceMatcherInterface
{
    /**
     * @readonly
     * @var \Rector\PSR4\Composer\PSR4AutoloadPathsProvider
     */
    private $psr4AutoloadPathsProvider;
    public function __construct(PSR4AutoloadPathsProvider $psr4AutoloadPathsProvider)
    {
        $this->psr4AutoloadPathsProvider = $psr4AutoloadPathsProvider;
    }
    public function getExpectedNamespace(File $file, Node $node) : ?string
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $psr4Autoloads = $this->psr4AutoloadPathsProvider->provide();
        foreach ($psr4Autoloads as $namespace => $path) {
            // remove extra slash
            $paths = \is_array($path) ? $path : [$path];
            foreach ($paths as $path) {
                $path = \rtrim($path, '/');
                if (\strncmp($smartFileInfo->getRelativeDirectoryPath(), $path, \strlen($path)) !== 0) {
                    continue;
                }
                $expectedNamespace = $namespace . $this->resolveExtraNamespace($smartFileInfo, $path);
                if (\strpos($expectedNamespace, '-') !== \false) {
                    return null;
                }
                return \rtrim($expectedNamespace, '\\');
            }
        }
        return null;
    }
    /**
     * Get the extra path that is not included in root PSR-4 namespace
     */
    private function resolveExtraNamespace(SmartFileInfo $smartFileInfo, string $path) : string
    {
        $extraNamespace = Strings::substring($smartFileInfo->getRelativeDirectoryPath(), Strings::length($path) + 1);
        $extraNamespace = Strings::replace($extraNamespace, '#/#', '\\');
        return \trim($extraNamespace);
    }
}
