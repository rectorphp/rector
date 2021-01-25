<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\FileSystem;

use Nette\Utils\Strings;
use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\TemplateFactory;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TemplateFileSystem
{
    /**
     * @var string
     * @see https://regex101.com/r/fw3jBe/1
     */
    private const FIXTURE_SHORT_REGEX = '#/Fixture/#';

    /**
     * @var string
     * @see https://regex101.com/r/HBcfXd/1
     */
    private const PACKAGE_RULES_PATH_REGEX = '#(packages|rules)\/__package__#i';

    /**
     * @var string
     * @see https://regex101.com/r/tOidWU/1
     */
    private const CONFIGURED_OR_EXTRA_REGEX = '#(__Configured|__Extra)#';

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    public function __construct(TemplateFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param array<string, mixed> $templateVariables
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
        $destination = $this->templateFactory->create($destination, $templateVariables);

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

    private function isNonFixtureFileWithIncSuffix(string $filePath): bool
    {
        if (Strings::match($filePath, self::FIXTURE_SHORT_REGEX)) {
            return false;
        }

        return Strings::endsWith($filePath, '.inc');
    }
}
