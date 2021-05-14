<?php

declare (strict_types=1);
namespace Rector\PSR4\Composer;

use RectorPrefix20210514\Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Symplify\SmartFileSystem\SmartFileInfo;
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
    public function getExpectedNamespace(\Rector\Core\ValueObject\Application\File $file, \PhpParser\Node $node) : ?string
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $psr4Autoloads = $this->psr4AutoloadPathsProvider->provide();
        foreach ($psr4Autoloads as $namespace => $path) {
            // remove extra slash
            $paths = \is_array($path) ? $path : [$path];
            foreach ($paths as $path) {
                $path = \rtrim($path, '/');
                if (!\RectorPrefix20210514\Nette\Utils\Strings::startsWith($smartFileInfo->getRelativeDirectoryPath(), $path)) {
                    continue;
                }
                $expectedNamespace = $namespace . $this->resolveExtraNamespace($smartFileInfo, $path);
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
        $extraNamespace = \RectorPrefix20210514\Nette\Utils\Strings::substring($smartFileInfo->getRelativeDirectoryPath(), \RectorPrefix20210514\Nette\Utils\Strings::length($path) + 1);
        $extraNamespace = \RectorPrefix20210514\Nette\Utils\Strings::replace($extraNamespace, '#/#', '\\');
        return \trim($extraNamespace);
    }
}
