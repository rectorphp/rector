<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Yaml\Form;

use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\ValueObject\Bridge;
use Ssch\TYPO3Rector\Contract\FileProcessor\Yaml\Form\FormYamlRectorInterface;
use Ssch\TYPO3Rector\FileProcessor\Yaml\YamlIndentResolver;
use RectorPrefix20220209\Symfony\Component\Yaml\Yaml;
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
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @var \Ssch\TYPO3Rector\FileProcessor\Yaml\YamlIndentResolver
     */
    private $yamlIndentResolver;
    /**
     * @var FormYamlRectorInterface[]
     */
    private $transformer;
    /**
     * @param FormYamlRectorInterface[] $transformer
     */
    public function __construct(\Rector\Core\Provider\CurrentFileProvider $currentFileProvider, \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory $fileDiffFactory, \Ssch\TYPO3Rector\FileProcessor\Yaml\YamlIndentResolver $yamlIndentResolver, array $transformer)
    {
        $this->currentFileProvider = $currentFileProvider;
        $this->fileDiffFactory = $fileDiffFactory;
        $this->yamlIndentResolver = $yamlIndentResolver;
        $this->transformer = $transformer;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [], \Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => []];
        $this->currentFileProvider->setFile($file);
        $smartFileInfo = $file->getSmartFileInfo();
        $oldYamlContent = $smartFileInfo->getContents();
        $yaml = \RectorPrefix20220209\Symfony\Component\Yaml\Yaml::parse($oldYamlContent);
        if (!\is_array($yaml)) {
            return $systemErrorsAndFileDiffs;
        }
        $newYaml = $yaml;
        foreach ($this->transformer as $transformer) {
            $newYaml = $transformer->refactor($newYaml);
        }
        // Nothing has changed. Early return here.
        if ($newYaml === $yaml) {
            return $systemErrorsAndFileDiffs;
        }
        $spaceCount = $this->yamlIndentResolver->resolveIndentSpaceCount($oldYamlContent);
        $newFileContent = \RectorPrefix20220209\Symfony\Component\Yaml\Yaml::dump($newYaml, 99, $spaceCount);
        $file->changeFileContent($newFileContent);
        $fileDiff = $this->fileDiffFactory->createFileDiff($file, $oldYamlContent, $newFileContent);
        $systemErrorsAndFileDiffs[\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS][] = $fileDiff;
        return $systemErrorsAndFileDiffs;
    }
    public function supports(\Rector\Core\ValueObject\Application\File $file, \Rector\Core\ValueObject\Configuration $configuration) : bool
    {
        // Prevent unnecessary processing
        if ([] === $this->transformer) {
            return \false;
        }
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
