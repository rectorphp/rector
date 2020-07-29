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
        if ($configuration->getPackage() === Package::UTILS) {
            // special keyword for 3rd party Rectors, not for core Github contribution
            $destination = Strings::replace($destination, '#packages\/__Package__#', 'utils/rector');
        }

        // remove _Configured|_Extra prefix
        $destination = $this->applyVariables($destination, $templateVariables);
        return Strings::replace($destination, '#(__Configured|__Extra)#', '');
    }

    /**
     * @param mixed[] $variables
     */
    private function applyVariables(string $content, array $variables): string
    {
        return str_replace(array_keys($variables), array_values($variables), $content);
    }
}
