<?php

declare(strict_types=1);

namespace Rector\Core\Bootstrap;

use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Set\RectorSetProvider;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\SetConfigResolver\ConfigResolver;
use Symplify\SetConfigResolver\SetAwareConfigResolver;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorConfigsResolver
{
    /**
     * @var ConfigResolver
     */
    private $configResolver;

    /**
     * @var SetAwareConfigResolver
     */
    private $setAwareConfigResolver;

    public function __construct()
    {
        $this->configResolver = new ConfigResolver();
        $rectorSetProvider = new RectorSetProvider();
        $this->setAwareConfigResolver = new SetAwareConfigResolver($rectorSetProvider);
    }

    /**
     * @noRector
     */
    public function getFirstResolvedConfig(): ?SmartFileInfo
    {
        return $this->configResolver->getFirstResolvedConfigFileInfo();
    }

    /**
     * @param SmartFileInfo[] $configFileInfos
     * @return SmartFileInfo[]
     */
    public function resolveSetFileInfosFromConfigFileInfos(array $configFileInfos): array
    {
        return $this->setAwareConfigResolver->resolveFromParameterSetsFromConfigFiles($configFileInfos);
    }

    /**
     * @return SmartFileInfo[]
     */
    public function provide(): array
    {
        $configFileInfos = [];

        $argvInput = new ArgvInput();
        $this->guardDeprecatedSetOption($argvInput);

        // And from --config or default one
        $inputOrFallbackConfigFileInfo = $this->configResolver->resolveFromInputWithFallback(
            $argvInput,
            ['rector.php']
        );

        if ($inputOrFallbackConfigFileInfo !== null) {
            $configFileInfos[] = $inputOrFallbackConfigFileInfo;
        }

        $setFileInfos = $this->resolveSetFileInfosFromConfigFileInfos($configFileInfos);

        if (in_array($argvInput->getFirstArgument(), ['generate', 'g', 'create', 'c'], true)) {
            // autoload rector recipe file if present, just for \Rector\RectorGenerator\Command\GenerateCommand
            $rectorRecipeFilePath = getcwd() . '/rector-recipe.php';
            if (file_exists($rectorRecipeFilePath)) {
                $configFileInfos[] = new SmartFileInfo($rectorRecipeFilePath);
            }
        }

        return array_merge($configFileInfos, $setFileInfos);
    }

    private function guardDeprecatedSetOption(InputInterface $input): void
    {
        $setOption = $input->getParameterOption(['-s', '--set']);
        if ($setOption === false) {
            return;
        }
        throw new ShouldNotHappenException(
            '"-s/--set" option was deprecated and removed. Use rector.php config and SetList class with autocomplete instead');
    }
}
