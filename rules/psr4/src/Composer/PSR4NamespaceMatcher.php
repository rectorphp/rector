<?php

declare(strict_types=1);

namespace Rector\PSR4\Composer;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\FileSystem\CurrentFileInfoProvider;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PSR4NamespaceMatcher implements PSR4AutoloadNamespaceMatcherInterface
{
    /**
     * @var PSR4AutoloadPathsProvider
     */
    private $psr4AutoloadPathsProvider;

    /**
     * @var CurrentFileInfoProvider
     */
    private $currentFileInfoProvider;

    public function __construct(
        PSR4AutoloadPathsProvider $psr4AutoloadPathsProvider,
        CurrentFileInfoProvider $currentFileInfoProvider
    ) {
        $this->psr4AutoloadPathsProvider = $psr4AutoloadPathsProvider;
        $this->currentFileInfoProvider = $currentFileInfoProvider;
    }

    public function getExpectedNamespace(Node $node): ?string
    {
        $smartFileInfo = $this->currentFileInfoProvider->getSmartFileInfo();
        if (! $smartFileInfo instanceof SmartFileInfo) {
            throw new ShouldNotHappenException();
        }

        $psr4Autoloads = $this->psr4AutoloadPathsProvider->provide();

        foreach ($psr4Autoloads as $namespace => $path) {
            // remove extra slash
            /** @var string[] $paths */
            $paths = is_array($path) ? $path : [$path];

            foreach ($paths as $singlePath) {
                $singlePath = rtrim($singlePath, '/');
                if (! Strings::startsWith($smartFileInfo->getRelativeDirectoryPath(), $singlePath)) {
                    continue;
                }

                $expectedNamespace = $namespace . $this->resolveExtraNamespace($smartFileInfo, $singlePath);

                return rtrim($expectedNamespace, '\\');
            }
        }

        return null;
    }

    /**
     * Get the extra path that is not included in root PSR-4 namespace
     */
    private function resolveExtraNamespace(SmartFileInfo $smartFileInfo, string $path): string
    {
        $extraNamespace = Strings::substring($smartFileInfo->getRelativeDirectoryPath(), Strings::length($path) + 1);
        $extraNamespace = Strings::replace($extraNamespace, '#/#', '\\');

        return trim($extraNamespace);
    }
}
