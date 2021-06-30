<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Yaml\Form;

use RectorPrefix20210630\Nette\Utils\Strings;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Ssch\TYPO3Rector\Contract\FileProcessor\Yaml\Form\FormYamlRectorInterface;
use RectorPrefix20210630\Symfony\Component\Yaml\Yaml;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Yaml\Form\FormYamlProcessorTest
 */
final class FormYamlProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
{
    /**
     * @var string[]
     */
    private const ALLOWED_FILE_EXTENSIONS = ['yaml'];
    /**
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @var mixed[]
     */
    private $transformer;
    /**
     * @param FormYamlRectorInterface[] $transformer
     */
    public function __construct(\Rector\Core\Provider\CurrentFileProvider $currentFileProvider, array $transformer)
    {
        $this->currentFileProvider = $currentFileProvider;
        $this->transformer = $transformer;
    }
    /**
     * @param File[] $files
     */
    public function process(array $files) : void
    {
        // Prevent unnecessary processing
        if ([] === $this->transformer) {
            return;
        }
        foreach ($files as $file) {
            $this->processFile($file);
        }
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return \RectorPrefix20210630\Nette\Utils\Strings::endsWith($smartFileInfo->getFilename(), 'yaml');
    }
    public function getSupportedFileExtensions() : array
    {
        return self::ALLOWED_FILE_EXTENSIONS;
    }
    private function processFile(\Rector\Core\ValueObject\Application\File $file) : void
    {
        $this->currentFileProvider->setFile($file);
        $smartFileInfo = $file->getSmartFileInfo();
        $yaml = \RectorPrefix20210630\Symfony\Component\Yaml\Yaml::parseFile($smartFileInfo->getRealPath());
        if (!\is_array($yaml)) {
            return;
        }
        $newYaml = $yaml;
        foreach ($this->transformer as $transformer) {
            $newYaml = $transformer->refactor($newYaml);
        }
        // Nothing has changed. Early return here.
        if ($newYaml === $yaml) {
            return;
        }
        $newFileContent = \RectorPrefix20210630\Symfony\Component\Yaml\Yaml::dump($newYaml, 99);
        $file->changeFileContent($newFileContent);
    }
}
