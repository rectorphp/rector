<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\FileSystem;

use Nette\Utils\Strings;
use Rector\Core\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TemplateFileSystem
{
    /**
     * @param string[] $templateVariables
     */
    public function resolveDestination(
        SmartFileInfo $smartFileInfo,
        array $templateVariables,
        RectorRecipe $rectorRecipe,
        string $targetDirectory
    ): string {
        $destination = $smartFileInfo->getRelativeFilePathFromDirectory(TemplateFinder::TEMPLATES_DIRECTORY);

        // normalize core package
        if (! $rectorRecipe->isRectorRepository()) {
            // special keyword for 3rd party Rectors, not for core Github contribution
            $destination = Strings::replace($destination, '#(packages|rules)\/__package__#i', 'utils/rector');
        }

        // remove _Configured|_Extra prefix
        $destination = $this->applyVariables($destination, $templateVariables);

        $destination = Strings::replace($destination, '#(__Configured|__Extra)#', '');

        // remove ".inc" protection from PHPUnit if not a test case
        if ($this->isNonFixtureFileWithIncSuffix($destination)) {
            $destination = Strings::before($destination, '.inc');
        }

        // special hack for tests, to PHPUnit doesn't load the generated file as test case
        /** @var string $destination */
        if (Strings::endsWith($destination, 'Test.php') && StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $destination .= '.inc';
        }

        return $targetDirectory . DIRECTORY_SEPARATOR . $destination;
    }

    /**
     * @param mixed[] $variables
     */
    private function applyVariables(string $content, array $variables): string
    {
        return str_replace(array_keys($variables), array_values($variables), $content);
    }

    private function isNonFixtureFileWithIncSuffix(string $filePath): bool
    {
        if (Strings::match($filePath, '#/Fixture/#')) {
            return false;
        }

        return Strings::endsWith($filePath, '.inc');
    }
}
