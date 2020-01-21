<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\FileSystem;

use Nette\Utils\Strings;
use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\ValueObject\Configuration;
use Rector\RectorGenerator\ValueObject\Package;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TemplateFileSystem
{
    /**
     * @param string[] $templateVariables
     */
    public function resolveDestination(
        SmartFileInfo $smartFileInfo,
        array $templateVariables,
        Configuration $configuration
    ): string {
        $destination = $smartFileInfo->getRelativeFilePathFromDirectory(TemplateFinder::TEMPLATES_DIRECTORY);

        // normalize core package
        if ($configuration->getPackage() === 'Rector') {
            $destination = Strings::replace($destination, '#packages\/_Package_/tests/Rector#', 'tests/Rector');
            $destination = Strings::replace($destination, '#packages\/_Package_/src/Rector#', 'src/Rector');
        }

        // special keyword for 3rd party Rectors, not for core Github contribution
        if ($configuration->getPackage() === Package::UTILS) {
            $destination = Strings::replace($destination, '#packages\/_Package_#', 'utils/rector');
        }

        if (! Strings::match($destination, '#fixture[\d+]*\.php\.inc#')) {
            $destination = rtrim($destination, '.inc');
        }

        return $this->applyVariables($destination, $templateVariables);
    }

    /**
     * @param mixed[] $variables
     */
    private function applyVariables(string $content, array $variables): string
    {
        return str_replace(array_keys($variables), array_values($variables), $content);
    }
}
