<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector;

use RectorPrefix20220501\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use RectorPrefix20220501\Helmich\TypoScriptParser\Parser\AST\Scalar as ScalarValue;
use Helmich\TypoScriptParser\Parser\AST\Statement;
use RectorPrefix20220501\Nette\Utils\Strings;
use PhpParser\Comment;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Contract\FileProcessor\TypoScript\ConvertToPhpFileInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
    public function __construct(\PHPStan\Reflection\ReflectionProvider $reflectionProvider, \Rector\Core\PhpParser\Printer\BetterStandardPrinter $betterStandardPrinter, \Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->nodeFactory = $nodeFactory;
        $this->filename = \getcwd() . '/Configuration_Extbase_Persistence_Classes.php';
    }
    public function enterNode(\Helmich\TypoScriptParser\Parser\AST\Statement $statement) : void
    {
        if (!$statement instanceof \RectorPrefix20220501\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment) {
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
        $persistenceArray = $this->nodeFactory->createArray([]);
        foreach (self::$persistenceArray as $class => $configuration) {
            $key = new \PhpParser\Node\Scalar\String_($class);
            if ($this->reflectionProvider->hasClass($class)) {
                $key = $this->nodeFactory->createClassConstReference($class);
            }
            $subArray = $this->nodeFactory->createArray([]);
            foreach (['recordType', 'tableName'] as $subKey) {
                if (\array_key_exists($subKey, $configuration)) {
                    $subArray->items[] = new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Scalar\String_($configuration[$subKey]), new \PhpParser\Node\Scalar\String_($subKey), \false, [\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS => [new \PhpParser\Comment(\PHP_EOL)]]);
                }
            }
            foreach ([self::PROPERTIES, 'subclasses'] as $subKey) {
                if (\array_key_exists($subKey, $configuration)) {
                    $subArray->items[] = new \PhpParser\Node\Expr\ArrayItem($this->nodeFactory->createArray($configuration[$subKey]), new \PhpParser\Node\Scalar\String_($subKey), \false, [\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS => [new \PhpParser\Comment(\PHP_EOL)]]);
                }
            }
            $persistenceArray->items[] = new \PhpParser\Node\Expr\ArrayItem($subArray, $key, \false, [\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS => [new \PhpParser\Comment(\PHP_EOL)]]);
        }
        $return = new \PhpParser\Node\Stmt\Return_($persistenceArray);
        $content = $this->betterStandardPrinter->prettyPrintFile([$return]);
        $content = \RectorPrefix20220501\Nette\Utils\Strings::replace($content, self::REMOVE_EMPTY_LINES, '');
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
    private function extractSubClasses(array $paths, \RectorPrefix20220501\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment $statement) : void
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
    private function extractMapping(string $name, array $paths, \RectorPrefix20220501\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment $statement) : void
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
    private function extractColumns(array $paths, \RectorPrefix20220501\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment $statement) : void
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
