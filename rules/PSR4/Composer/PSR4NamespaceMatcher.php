<?php

declare(strict_types=1);

namespace Rector\PSR4\Composer;

use Nette\Utils\Strings;
use PhpParser\Node;
use Rector\Core\ValueObject\Application\File;
use Rector\PSR4\Contract\PSR4AutoloadNamespaceMatcherInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PSR4NamespaceMatcher implements PSR4AutoloadNamespaceMatcherInterface
{
    public function __construct(
        private PSR4AutoloadPathsProvider $psr4AutoloadPathsProvider
    ) {
    }

    public function getExpectedNamespace(File $file, Node $node): ?string
    {
        $smartFileInfo = $file->getSmartFileInfo();
        $psr4Autoloads = $this->psr4AutoloadPathsProvider->provide();

        foreach ($psr4Autoloads as $namespace => $path) {
            // remove extra slash
            $paths = is_array($path) ? $path : [$path];

            foreach ($paths as $path) {
                $path = rtrim($path, '/');
                if (! Strings::startsWith($smartFileInfo->getRelativeDirectoryPath(), $path)) {
                    continue;
                }

                $expectedNamespace = $namespace . $this->resolveExtraNamespace($smartFileInfo, $path);

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
