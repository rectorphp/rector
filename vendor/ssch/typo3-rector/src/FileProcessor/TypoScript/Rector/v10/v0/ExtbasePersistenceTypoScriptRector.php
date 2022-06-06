<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\v10\v0;

use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use RectorPrefix20220606\Helmich\TypoScriptParser\Parser\AST\Scalar as ScalarValue;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\PhpParser\Comment;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Return_;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use RectorPrefix20220606\Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface;
use RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\AbstractTypoScriptRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-87623-ReplaceConfigpersistenceclassesTyposcriptConfiguration.html
 * @see \Ssch\TYPO3Rector\Tests\FileProcessor\TypoScript\TypoScriptProcessorTest
 */
final class ExtbasePersistenceTypoScriptRector extends AbstractTypoScriptRector implements ConvertToPhpFileInterface, ConfigurableRectorInterface
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
    private const REMOVE_EMPTY_LINES = '/^[ \\t]*[\\r\\n]+/m';
    /**
     * @var string
     */
    private const PROPERTIES = 'properties';
    /**
     * @var string
     */
    private $filename;
    /**
     * @var array<string, array<string, mixed>>
     */
    private static $persistenceArray = [];
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Printer\BetterStandardPrinter
     */
    private $betterStandardPrinter;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(ReflectionProvider $reflectionProvider, BetterStandardPrinter $betterStandardPrinter, NodeFactory $nodeFactory)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeFactory = $nodeFactory;
        $this->filename = \getcwd() . '/Configuration_Extbase_Persistence_Classes.php';
    }
    public function enterNode(Statement $statement) : void
    {
        if (!$statement instanceof Assignment) {
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Convert extbase TypoScript persistence configuration to classes one', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
    public function convert() : ?AddedFileWithContent
    {
        if ([] === self::$persistenceArray) {
            return null;
        }
        $persistenceArray = $this->nodeFactory->createArray([]);
        foreach (self::$persistenceArray as $class => $configuration) {
            $key = new String_($class);
            if ($this->reflectionProvider->hasClass($class)) {
                $key = $this->nodeFactory->createClassConstReference($class);
            }
            $subArray = $this->nodeFactory->createArray([]);
            foreach (['recordType', 'tableName'] as $subKey) {
                if (\array_key_exists($subKey, $configuration)) {
                    $subArray->items[] = new ArrayItem(new String_($configuration[$subKey]), new String_($subKey), \false, [AttributeKey::COMMENTS => [new Comment(\PHP_EOL)]]);
                }
            }
            foreach ([self::PROPERTIES, 'subclasses'] as $subKey) {
                if (\array_key_exists($subKey, $configuration)) {
                    $subArray->items[] = new ArrayItem($this->nodeFactory->createArray($configuration[$subKey]), new String_($subKey), \false, [AttributeKey::COMMENTS => [new Comment(\PHP_EOL)]]);
                }
            }
            $persistenceArray->items[] = new ArrayItem($subArray, $key, \false, [AttributeKey::COMMENTS => [new Comment(\PHP_EOL)]]);
        }
        $return = new Return_($persistenceArray);
        $content = $this->betterStandardPrinter->prettyPrintFile([$return]);
        $content = Strings::replace($content, self::REMOVE_EMPTY_LINES, '');
        return new AddedFileWithContent($this->filename, $content);
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
    private function extractSubClasses(array $paths, Assignment $statement) : void
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
        self::$persistenceArray[$className][self::SUBCLASSES][$statement->object->relativeName] = $scalarValue->value;
    }
    /**
     * @param string[] $paths
     */
    private function extractMapping(string $name, array $paths, Assignment $statement) : void
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
    private function extractColumns(array $paths, Assignment $statement) : void
    {
        if (!\in_array('columns', $paths, \true)) {
            return;
        }
        if (isset($paths[4]) && 'config' === $paths[4]) {
            return;
        }
        if (isset($paths[5]) && 'type' === $paths[5]) {
            return;
        }
        $className = $paths[0];
        if (!\array_key_exists($className, self::$persistenceArray)) {
            self::$persistenceArray[$className][self::PROPERTIES] = [];
        }
        $fieldName = $paths[3];
        /** @var ScalarValue $scalar */
        $scalar = $statement->value;
        self::$persistenceArray[$className][self::PROPERTIES][$scalar->value]['fieldName'] = $fieldName;
    }
}
