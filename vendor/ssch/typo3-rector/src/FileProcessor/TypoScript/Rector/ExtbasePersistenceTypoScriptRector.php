<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector;

use RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\Scalar as ScalarValue;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20211231\Nette\Utils\Strings;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface;
use Ssch\TYPO3Rector\Template\TemplateFinder;
use RectorPrefix20211231\Symfony\Component\VarExporter\VarExporter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-87623-ReplaceConfigpersistenceclassesTyposcriptConfiguration.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\TypoScript\TypoScriptProcessorTest
 */
final class ExtbasePersistenceTypoScriptRector extends \Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector implements \Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface, \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FILENAME = 'filename';
    /**
     * @var string
     */
    private const SUBCLASSES = 'subclasses';
    /**
     * @var string
     */
    private $filename;
    /**
     * @var array<string, array<string, mixed>>
     */
    private static $persistenceArray = [];
    /**
     * @var \Symplify\SmartFileSystem\SmartFileInfo
     */
    private $fileTemplate;
    public function __construct(\Ssch\TYPO3Rector\Template\TemplateFinder $templateFinder)
    {
        $this->filename = \getcwd() . '/Configuration_Extbase_Persistence_Classes.php';
        $this->fileTemplate = $templateFinder->getExtbasePersistenceConfiguration();
    }
    public function enterNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        if (!$statement instanceof \RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment) {
            return;
        }
        if (\strpos($statement->object->absoluteName, 'persistence.classes') === \false) {
            return;
        }
        $paths = \explode('.', $statement->object->absoluteName);
        // Strip the first parts like config.tx_extbase.persistence.classes
        $paths = \array_slice($paths, 4);
        $this->extractSubClasses($paths, $statement);
        $this->extractMapping('tableName', $paths, $statement);
        $this->extractMapping('recordType', $paths, $statement);
        $this->extractColumns($paths, $statement);
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Convert extbase TypoScript persistence configuration to classes one', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
config.tx_extbase.persistence.classes {
    GeorgRinger\News\Domain\Model\FileReference {
        mapping {
            tableName = sys_file_reference
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
return [
    \GeorgRinger\News\Domain\Model\FileReference::class => [
        'tableName' => 'sys_file_reference',
    ],
];
CODE_SAMPLE
, [self::FILENAME => 'path/to/Configuration/Extbase/Persistence/Classes.php'])]);
    }
    public function convert() : ?\Rector\FileSystemRector\ValueObject\AddedFileWithContent
    {
        if ([] === self::$persistenceArray) {
            return null;
        }
        $content = \str_replace('__PERSISTENCE_ARRAY__', \RectorPrefix20211231\Symfony\Component\VarExporter\VarExporter::export(self::$persistenceArray), $this->fileTemplate->getContents());
        $content = \RectorPrefix20211231\Nette\Utils\Strings::replace($content, "#'(.*\\\\.*)'#mU", function (array $match) : string {
            $string = \str_replace('\\\\', '\\', $match[1]);
            return \sprintf('\\%s::class', $string);
        });
        return new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($this->filename, $content);
    }
    public function getMessage() : string
    {
        return 'We have converted from TypoScript extbase persistence to a PHP File';
    }
    public function configure(array $configuration) : void
    {
        $filename = $configuration[self::FILENAME] ?? null;
        if (null !== $filename) {
            $this->filename = $filename;
        }
    }
    /**
     * @param string[] $paths
     */
    private function extractSubClasses(array $paths, \RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment $statement) : void
    {
        if (!\in_array(self::SUBCLASSES, $paths, \true)) {
            return;
        }
        $className = $paths[0];
        if (!\array_key_exists($className, self::$persistenceArray)) {
            self::$persistenceArray[$className] = [self::SUBCLASSES => []];
        }
        /** @var ScalarValue $scalarValue */
        $scalarValue = $statement->value;
        self::$persistenceArray[$className][self::SUBCLASSES][] = $scalarValue->value;
    }
    /**
     * @param string[] $paths
     */
    private function extractMapping(string $name, array $paths, \RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment $statement) : void
    {
        if (!\in_array($name, $paths, \true)) {
            return;
        }
        $className = $paths[0];
        if (!\array_key_exists($className, self::$persistenceArray)) {
            self::$persistenceArray[$className] = [];
        }
        /** @var ScalarValue $scalar */
        $scalar = $statement->value;
        self::$persistenceArray[$className][$name] = $scalar->value;
    }
    /**
     * @param string[] $paths
     */
    private function extractColumns(array $paths, \RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment $statement) : void
    {
        if (!\in_array('columns', $paths, \true)) {
            return;
        }
        $className = $paths[0];
        if (!\array_key_exists($className, self::$persistenceArray)) {
            self::$persistenceArray[$className]['properties'] = [];
        }
        $fieldName = $paths[3];
        /** @var ScalarValue $scalar */
        $scalar = $statement->value;
        self::$persistenceArray[$className]['properties'][$scalar->value]['fieldName'] = $fieldName;
    }
}
