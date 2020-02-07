<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Config;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\RectorGenerator\TemplateFactory;
use Rector\RectorGenerator\ValueObject\Configuration;

final class ConfigFilesystem
{
    /**
     * @var string
     */
    private const RECTOR_FQN_NAME_PATTERN = 'Rector\_Package_\Rector\_Category_\_Name_';

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    public function __construct(TemplateFactory $templateFactory)
    {
        $this->templateFactory = $templateFactory;
    }

    /**
     * @param string[] $templateVariables
     */
    public function appendRectorServiceToSet(Configuration $configuration, array $templateVariables): void
    {
        if ($configuration->getSetConfig() === null) {
            return;
        }

        if (! file_exists($configuration->getSetConfig())) {
            return;
        }

        $setConfigContent = FileSystem::read($configuration->getSetConfig());

        // already added
        $rectorFqnName = $this->templateFactory->create(self::RECTOR_FQN_NAME_PATTERN, $templateVariables);
        if (Strings::contains($setConfigContent, $rectorFqnName)) {
            return;
        }

        $newSetConfigContent = trim($setConfigContent) . sprintf(
            '%s%s: null%s',
            PHP_EOL,
            $this->indentFourSpaces($rectorFqnName),
            PHP_EOL
        );

        FileSystem::write($configuration->getSetConfig(), $newSetConfigContent);
    }

    private function indentFourSpaces(string $string): string
    {
        return Strings::indent($string, 4, ' ');
    }
}
