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
     * @var string
     */
    private const FIXTURE_SHORT_REGEX = '#/Fixture/#';

    /**
     * @var string
     */
    private const PACKAGE_RULES_PATH_REGEX = '#(packages|rules)\/__package__#i';

    /**
     * @var string
     */
    private const CONFIGURED_OR_EXTRA_REGEX = '#(__Configured|__Extra)#';

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
            $destination = Strings::replace($destination, self::PACKAGE_RULES_PATH_REGEX, 'utils/rector');
        }

        // remove _Configured|_Extra prefix
        $destination = $this->applyVariables($destination, $templateVariables);

        $destination = Strings::replace($destination, self::CONFIGURED_OR_EXTRA_REGEX, '');

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
        if (Strings::match($filePath, self::FIXTURE_SHORT_REGEX)) {
            return false;
        }

        return Strings::endsWith($filePath, '.inc');
    }
}
