<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\FileSystem;

use RectorPrefix20220418\Nette\Utils\Strings;
use Rector\RectorGenerator\Exception\ShouldNotHappenException;
use Rector\RectorGenerator\TemplateFactory;
use RectorPrefix20220418\Symplify\SmartFileSystem\SmartFileSystem;
final class ConfigFilesystem
{
    /**
     * @var string[]
     */
    private const REQUIRED_KEYS = ['__Package__', '__Category__', '__Name__'];
    /**
     * @see https://regex101.com/r/gJ0bHJ/1
     */
    private const LAST_ITEM_REGEX = '#;\\n};#';
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @readonly
     * @var \Rector\RectorGenerator\TemplateFactory
     */
    private $templateFactory;
    public function __construct(\RectorPrefix20220418\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Rector\RectorGenerator\TemplateFactory $templateFactory)
    {
        $this->smartFileSystem = $smartFileSystem;
        $this->templateFactory = $templateFactory;
    }
    /**
     * @param array<string, string> $templateVariables
     */
    public function appendRectorServiceToSet(string $setFilePath, array $templateVariables, string $rectorFqnNamePattern) : void
    {
        $setFileContents = $this->smartFileSystem->readFile($setFilePath);
        $this->ensureRequiredKeysAreSet($templateVariables);
        // already added?
        $servicesFullyQualifiedName = $this->templateFactory->create($rectorFqnNamePattern, $templateVariables);
        if (\strpos($setFileContents, $servicesFullyQualifiedName) !== \false) {
            return;
        }
        $registerServiceLine = \sprintf(';' . \PHP_EOL . '    $services->set(\\%s::class);' . \PHP_EOL . '};', $servicesFullyQualifiedName);
        $setFileContents = \RectorPrefix20220418\Nette\Utils\Strings::replace($setFileContents, self::LAST_ITEM_REGEX, $registerServiceLine);
        // 3. print the content back to file
        $this->smartFileSystem->dumpFile($setFilePath, $setFileContents);
    }
    /**
     * @param array<string, string> $templateVariables
     */
    private function ensureRequiredKeysAreSet(array $templateVariables) : void
    {
        $missingKeys = \array_diff(self::REQUIRED_KEYS, \array_keys($templateVariables));
        if ($missingKeys === []) {
            return;
        }
        $message = \sprintf('Template variables for "%s" keys are missing', \implode('", "', $missingKeys));
        throw new \Rector\RectorGenerator\Exception\ShouldNotHappenException($message);
    }
}
