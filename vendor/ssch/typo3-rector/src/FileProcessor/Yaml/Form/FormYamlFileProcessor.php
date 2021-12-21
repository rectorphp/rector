<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Yaml\Form;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Ssch\TYPO3Rector\Contract\FileProcessor\Yaml\Form\FormYamlRectorInterface;
use RectorPrefix20211221\Symfony\Component\Yaml\Yaml;
/**
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\Yaml\Form\FormYamlProcessorTest
 */
final class FormYamlFileProcessor implements \Rector\Core\Contract\Processor\FileProcessorInterface
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
     * @var \Ssch\TYPO3Rector\Contract\FileProcessor\Yaml\Form\FormYamlRectorInterface[]
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
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        // Prevent unnecessary processing
        if ([] === $this->transformer) {
            return;
        }
        $this->currentFileProvider->setFile($file);
        $smartFileInfo = $file->getSmartFileInfo();
        $yaml = \RectorPrefix20211221\Symfony\Component\Yaml\Yaml::parseFile($smartFileInfo->getRealPath());
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
        $newFileContent = \RectorPrefix20211221\Symfony\Component\Yaml\Yaml::dump($newYaml, 99);
        $file->changeFileContent($newFileContent);
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool
    {
        $smartFileInfo = $file->getSmartFileInfo();
        return \substr_compare($smartFileInfo->getFilename(), 'yaml', -\strlen('yaml')) === 0;
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return self::ALLOWED_FILE_EXTENSIONS;
    }
}
