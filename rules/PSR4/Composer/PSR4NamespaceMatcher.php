<?php

declare (strict_types=1);
namespace Rector\PSR4\Composer;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Rector\Tests\PSR4\Composer\PSR4NamespaceMatcherTest
 */
final class PSR4NamespaceMatcher implements \Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface
{
    /**
     * @var \Rector\PSR4\Composer\PSR4AutoloadPathsProvider
     */
    private $psr4AutoloadPathsProvider;
    public function __construct(\Rector\PSR4\Composer\PSR4AutoloadPathsProvider $psr4AutoloadPathsProvider)
    {
        $this->psr4AutoloadPathsProvider = $psr4AutoloadPathsProvider;
    }
    /**
     * @param \Rector\Core\ValueObject\Application\File $file
     * @param \PhpParser\Node $node
     */
    public function getExpectedNamespace($file, $node) : ?string
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
    private function resolveExtraNamespace(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, string $path) : string
    {
        $extraNamespace = \RectorPrefix20211020\Nette\Utils\Strings::substring($smartFileInfo->getRelativeDirectoryPath(), \RectorPrefix20211020\Nette\Utils\Strings::length($path) + 1);
        $extraNamespace = \RectorPrefix20211020\Nette\Utils\Strings::replace($extraNamespace, '#/#', '\\');
        return \trim($extraNamespace);
    }
}
