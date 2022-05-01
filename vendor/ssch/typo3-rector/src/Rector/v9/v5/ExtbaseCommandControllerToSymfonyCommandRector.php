<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v5;

use RectorPrefix20220501\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode;
use PHPStan\Type\ObjectType;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Contract\PhpParser\NodePrinterInterface;
use Rector\Core\PhpParser\Parser\RectorParser;
use Rector\Core\PhpParser\Parser\SimplePhpParser;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use Ssch\TYPO3Rector\Helper\FilesFinder;
use Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddArgumentToSymfonyCommandRector;
use Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddCommandsToReturnRector;
use Ssch\TYPO3Rector\Template\TemplateFinder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
use RectorPrefix20220501\Symplify\SmartFileSystem\SmartFileSystem;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-coreapi/9.5/en-us/ApiOverview/CommandControllers/Index.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommandRector\ExtbaseCommandControllerToSymfonyCommandRectorTest
 */
final class ExtbaseCommandControllerToSymfonyCommandRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const REMOVE_EMPTY_LINES = '/^[ \\t]*[\\r\\n]+/m';
    /**
     * @readonly
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddArgumentToSymfonyCommandRector
     */
    private $addArgumentToSymfonyCommandRector;
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\FilesFinder
     */
    private $filesFinder;
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddCommandsToReturnRector
     */
    private $addCommandsToReturnRector;
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\SimplePhpParser
     */
    private $simplePhpParser;
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Template\TemplateFinder
     */
    private $templateFinder;
    /**
     * @readonly
     * @var \Rector\Core\Contract\PhpParser\NodePrinterInterface
     */
    private $nodePrinter;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector
     */
    private $removedAndAddedFilesCollector;
    public function __construct(\RectorPrefix20220501\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Rector\Core\PhpParser\Parser\RectorParser $rectorParser, \Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddArgumentToSymfonyCommandRector $addArgumentToSymfonyCommandRector, \Ssch\TYPO3Rector\Helper\FilesFinder $filesFinder, \Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddCommandsToReturnRector $addCommandsToReturnRector, \Rector\Core\PhpParser\Parser\SimplePhpParser $simplePhpParser, \Ssch\TYPO3Rector\Template\TemplateFinder $templateFinder, \Rector\Core\Contract\PhpParser\NodePrinterInterface $nodePrinter, \Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector $removedAndAddedFilesCollector)
    {
        $this->smartFileSystem = $smartFileSystem;
        $this->rectorParser = $rectorParser;
        $this->addArgumentToSymfonyCommandRector = $addArgumentToSymfonyCommandRector;
        $this->filesFinder = $filesFinder;
        $this->addCommandsToReturnRector = $addCommandsToReturnRector;
        $this->simplePhpParser = $simplePhpParser;
        $this->templateFinder = $templateFinder;
        $this->nodePrinter = $nodePrinter;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\CommandController'))) {
            return null;
        }
        $commandMethods = $this->findCommandMethods($node);
        if ([] === $commandMethods) {
            return null;
        }
        if ([] === $node->namespacedName->parts) {
            return null;
        }
        // This is super hacky, but for now i have no other idea to test it here
        $currentSmartFileInfo = $this->file->getSmartFileInfo();
        $extEmConfFileInfo = $this->filesFinder->findExtEmConfRelativeFromGivenFileInfo($currentSmartFileInfo);
        if (!$extEmConfFileInfo instanceof \Symplify\SmartFileSystem\SmartFileInfo) {
            return null;
        }
        $extensionDirectory = \dirname($extEmConfFileInfo->getRealPath());
        $commandsFilePath = \sprintf('%s/Configuration/Commands.php', $extensionDirectory);
        $namespaceParts = $node->namespacedName->parts;
        $vendorName = \array_shift($namespaceParts);
        $extensionName = \array_shift($namespaceParts);
        $commandNamespace = \sprintf('\%s\\%s\\Command', $vendorName, $extensionName);
        // Collect all new commands
        $newCommandsWithFullQualifiedNamespace = [];
        foreach ($commandMethods as $commandMethod) {
            if (!$commandMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
                continue;
            }
            $commandMethodName = $this->getName($commandMethod->name);
            if (null === $commandMethodName) {
                continue;
            }
            if (null === $commandMethod->stmts) {
                continue;
            }
            $commandPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($commandMethod);
            $paramTags = $commandPhpDocInfo->getParamTagValueNodes();
            /** @var PhpDocTextNode[] $descriptionPhpDocNodes */
            $descriptionPhpDocNodes = $commandPhpDocInfo->getByType(\PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTextNode::class);
            $methodParameters = $commandMethod->params;
            if (!isset($descriptionPhpDocNodes[0])) {
                continue;
            }
            $commandDescription = $descriptionPhpDocNodes[0]->text;
            $commandTemplate = $this->templateFinder->getCommand();
            $commandName = \RectorPrefix20220501\Nette\Utils\Strings::firstUpper($commandMethodName);
            $commandContent = $commandTemplate->getContents();
            $filePath = \sprintf('%s/Classes/Command/%s.php', $extensionDirectory, $commandName);
            // Do not overwrite existing file
            if ($this->smartFileSystem->exists($filePath) && !\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
                continue;
            }
            $commandVariables = ['__TEMPLATE_NAMESPACE__' => \ltrim($commandNamespace, '\\'), '__TEMPLATE_COMMAND_NAME__' => $commandName, '__TEMPLATE_DESCRIPTION__' => $commandDescription, '__TEMPLATE_COMMAND_BODY__' => $this->nodePrinter->prettyPrint($commandMethod->stmts)];
            // Add traits, other methods etc. to class
            // Maybe inject dependencies into __constructor
            $commandContent = \str_replace(\array_keys($commandVariables), $commandVariables, $commandContent);
            $stmts = $this->simplePhpParser->parseString($commandContent);
            $this->decorateNamesToFullyQualified($stmts);
            $nodeTraverser = new \PhpParser\NodeTraverser();
            $inputArguments = [];
            foreach ($methodParameters as $key => $methodParameter) {
                $paramTag = $paramTags[$key] ?? null;
                $methodParamName = $this->nodeNameResolver->getName($methodParameter->var);
                if (null === $methodParamName) {
                    continue;
                }
                $inputArguments[$methodParamName] = ['name' => $methodParamName, 'description' => null !== $paramTag ? $paramTag->description : '', 'mode' => null !== $methodParameter->default ? 2 : 1, 'default' => $methodParameter->default];
            }
            $this->addArgumentToSymfonyCommandRector->configure([\Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddArgumentToSymfonyCommandRector::INPUT_ARGUMENTS => $inputArguments]);
            $nodeTraverser->addVisitor($this->addArgumentToSymfonyCommandRector);
            /** @var Stmt[] $stmts */
            $stmts = $nodeTraverser->traverse($stmts);
            $changedSetConfigContent = $this->nodePrinter->prettyPrintFile($stmts);
            $this->removedAndAddedFilesCollector->addAddedFile(new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($filePath, $changedSetConfigContent));
            $newCommandName = \sprintf('%s:%s', \RectorPrefix20220501\Nette\Utils\Strings::lower($vendorName), \RectorPrefix20220501\Nette\Utils\Strings::lower($commandName));
            $newCommandsWithFullQualifiedNamespace[$newCommandName] = \sprintf('%s\\%s', $commandNamespace, $commandName);
        }
        $this->addNewCommandsToCommandsFile($commandsFilePath, $newCommandsWithFullQualifiedNamespace);
        $this->addArgumentToSymfonyCommandRector->configure([\Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddArgumentToSymfonyCommandRector::INPUT_ARGUMENTS => []]);
        $this->addCommandsToReturnRector->configure([\Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddCommandsToReturnRector::COMMANDS => []]);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate from extbase CommandController to Symfony Command', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

final class TestCommand extends CommandController
{
    /**
     * This is the description of the command
     *
     * @param string Foo The foo parameter
     */
    public function fooCommand(string $foo)
    {

    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class FooCommand extends Command
{
    protected function configure(): void
    {
        $this->setDescription('This is the description of the command');
        $this->addArgument('foo', \Symfony\Component\Console\Input\InputArgument::REQUIRED, 'The foo parameter', null);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return 0;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return Node[]|ClassMethod[]
     */
    private function findCommandMethods(\PhpParser\Node\Stmt\Class_ $class) : array
    {
        return $this->betterNodeFinder->find($class->stmts, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return \false;
            }
            if (!$node->isPublic()) {
                return \false;
            }
            $methodName = $this->getName($node->name);
            if (null === $methodName) {
                return \false;
            }
            return \substr_compare($methodName, 'Command', -\strlen('Command')) === 0;
        });
    }
    /**
     * @param array<string, string> $newCommandsWithFullQualifiedNamespace
     */
    private function addNewCommandsToCommandsFile(string $commandsFilePath, array $newCommandsWithFullQualifiedNamespace) : void
    {
        if ($this->smartFileSystem->exists($commandsFilePath)) {
            $commandsSmartFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($commandsFilePath);
            $stmts = $this->rectorParser->parseFile($commandsSmartFileInfo);
        } else {
            $stmts = [new \PhpParser\Node\Stmt\Return_($this->nodeFactory->createArray([]))];
        }
        $this->decorateNamesToFullyQualified($stmts);
        $nodeTraverser = new \PhpParser\NodeTraverser();
        $this->addCommandsToReturnRector->configure([\Ssch\TYPO3Rector\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommand\AddCommandsToReturnRector::COMMANDS => $newCommandsWithFullQualifiedNamespace]);
        $nodeTraverser->addVisitor($this->addCommandsToReturnRector);
        /** @var Stmt[] $stmts */
        $stmts = $nodeTraverser->traverse($stmts);
        $changedCommandsContent = $this->nodePrinter->prettyPrintFile($stmts);
        $changedCommandsContent = \RectorPrefix20220501\Nette\Utils\Strings::replace($changedCommandsContent, self::REMOVE_EMPTY_LINES, '');
        $this->removedAndAddedFilesCollector->addAddedFile(new \Rector\FileSystemRector\ValueObject\AddedFileWithContent($commandsFilePath, $changedCommandsContent));
    }
    /**
     * @param Stmt[] $stmts
     */
    private function decorateNamesToFullyQualified(array $stmts) : void
    {
        // decorate nodes with names first
        $nameResolverNodeTraverser = new \PhpParser\NodeTraverser();
        $nameResolverNodeTraverser->addVisitor(new \PhpParser\NodeVisitor\NameResolver());
        $nameResolverNodeTraverser->traverse($stmts);
    }
}
