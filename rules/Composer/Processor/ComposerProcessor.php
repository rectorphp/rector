<?php

declare(strict_types=1);

namespace Rector\Composer\Processor;

use Rector\Composer\Modifier\ComposerModifier;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Processor\NonPhpFileProcessorInterface;
use Symfony\Component\Process\Process;
use Symplify\ComposerJsonManipulator\ComposerJsonFactory;
use Symplify\ComposerJsonManipulator\Printer\ComposerJsonPrinter;
use Symplify\ComposerJsonManipulator\ValueObject\ComposerJson;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ComposerProcessor implements NonPhpFileProcessorInterface
{
    /**
     * @var string
     */
    private const COMPOSER_UPDATE = 'composer update';

    /**
     * @var ComposerJsonFactory
     */
    private $composerJsonFactory;

    /**
     * @var ComposerJsonPrinter
     */
    private $composerJsonPrinter;

    /**
     * @var ComposerModifier
     */
    private $composerModifier;

    /**
     * @var Configuration
     */
    private $configuration;

    public function __construct(
        ComposerJsonFactory $composerJsonFactory,
        ComposerJsonPrinter $composerJsonPrinter,
        Configuration $configuration,
        ComposerModifier $composerModifier
    ) {
        $this->composerJsonFactory = $composerJsonFactory;
        $this->composerJsonPrinter = $composerJsonPrinter;
        $this->configuration = $configuration;
        $this->composerModifier = $composerModifier;
    }

    public function process(SmartFileInfo $smartFileInfo): ?string
    {
        // to avoid modification of file
        if (! $this->composerModifier->enabled()) {
            return null;
        }

        $composerJson = $this->composerJsonFactory->createFromFileInfo($smartFileInfo);
        $oldComposerJson = clone $composerJson;
        $this->composerModifier->modify($composerJson);

        // nothing has changed
        if ($oldComposerJson->getJsonArray() === $composerJson->getJsonArray()) {
            return null;
        }
        $newContent = $this->composerJsonPrinter->printToString($composerJson);

        $this->reportFileContentChange($composerJson, $smartFileInfo);

        return $newContent;
    }

    public function canProcess(SmartFileInfo $smartFileInfo): bool
    {
        return $smartFileInfo->getRealPath() === getcwd() . '/composer.json';
    }

    public function allowedFileExtensions(): array
    {
        return ['json'];
    }

    public function transformOldContent(SmartFileInfo $smartFileInfo): string
    {
        $oldComposerJson = $this->composerJsonFactory->createFromFileInfo($smartFileInfo);

        return $this->composerJsonPrinter->printToString($oldComposerJson);
    }

    private function reportFileContentChange(ComposerJson $composerJson, SmartFileInfo $smartFileInfo): void
    {
        if ($this->configuration->isDryRun()) {
            return;
        }

        $this->composerJsonPrinter->print($composerJson, $smartFileInfo);

        $process = new Process(explode(' ', self::COMPOSER_UPDATE), getcwd());
        $process->run(function (string $type, string $message): void {
            // $type is always err https://github.com/composer/composer/issues/3795#issuecomment-76401013
            echo $message;
        });
    }
}
