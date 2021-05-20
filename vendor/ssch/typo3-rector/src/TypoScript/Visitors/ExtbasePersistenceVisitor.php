<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\TypoScript\Visitors;

use RectorPrefix20210520\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use RectorPrefix20210520\Helmich\TypoScriptParser\Parser\AST\Scalar as ScalarValue;
use RectorPrefix20210520\Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20210520\Nette\Utils\Strings;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Ssch\TYPO3Rector\Contract\TypoScript\ConvertToPhpFileInterface;
use RectorPrefix20210520\Symfony\Component\VarExporter\VarExporter;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-87623-ReplaceConfigpersistenceclassesTyposcriptConfiguration.html
 */
final class ExtbasePersistenceVisitor extends \Ssch\TYPO3Rector\TypoScript\Visitors\AbstractVisitor implements \Ssch\TYPO3Rector\Contract\TypoScript\ConvertToPhpFileInterface, \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const FILENAME = 'filename';
    /**
     * @var string
     */
    private const GENERATED_FILE_TEMPLATE = <<<'CODE_SAMPLE'
<?php

declare(strict_types = 1);

return %s;

CODE_SAMPLE;
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
    public function __construct(\Rector\Core\Configuration\Configuration $configuration)
    {
        $this->filename = \dirname((string) $configuration->getMainConfigFilePath()) . '/Configuration_Extbase_Persistence_Classes.php';
    }
    public function enterNode(\RectorPrefix20210520\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        if (!$statement instanceof \RectorPrefix20210520\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment) {
            return;
        }
        if (!\RectorPrefix20210520\Nette\Utils\Strings::contains($statement->object->absoluteName, 'persistence.classes')) {
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
        $content = \sprintf(self::GENERATED_FILE_TEMPLATE, \RectorPrefix20210520\Symfony\Component\VarExporter\VarExporter::export(self::$persistenceArray));
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
    private function extractSubClasses(array $paths, \RectorPrefix20210520\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment $statement) : void
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
    private function extractMapping(string $name, array $paths, \RectorPrefix20210520\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment $statement) : void
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
    private function extractColumns(array $paths, \RectorPrefix20210520\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment $statement) : void
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
        self::$persistenceArray[$className]['properties'][$scalar->value]['fieldname'] = $fieldName;
    }
}
