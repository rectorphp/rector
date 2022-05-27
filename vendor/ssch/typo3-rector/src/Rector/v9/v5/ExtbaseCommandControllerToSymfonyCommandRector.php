<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v5;

use RectorPrefix20220527\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
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
use Ssch\TYPO3Rector\NodeAnalyzer\CommandArrayDecorator;
use Ssch\TYPO3Rector\NodeAnalyzer\CommandMethodDecorator;
use Ssch\TYPO3Rector\Template\TemplateFinder;
use RectorPrefix20220527\Symfony\Component\Console\Input\InputArgument;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @changelog https://docs.typo3.org/m/typo3/reference-coreapi/9.5/en-us/ApiOverview/CommandControllers/Index.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v5\ExtbaseCommandControllerToSymfonyCommandRector\ExtbaseCommandControllerToSymfonyCommandRectorTest
 */
final class ExtbaseCommandControllerToSymfonyCommandRector extends AbstractRector
{
    /**
     * @var string
     */
    private const REMOVE_EMPTY_LINES = '/^[ \\t]*[\\r\\n]+/m';
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Parser\RectorParser
     */
    private $rectorParser;
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\FilesFinder
     */
    private $filesFinder;
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
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\NodeAnalyzer\CommandArrayDecorator
     */
    private $commandArrayDecorator;
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\NodeAnalyzer\CommandMethodDecorator
     */
    private $commandMethodDecorator;
    public function __construct(RectorParser $rectorParser, FilesFinder $filesFinder, SimplePhpParser $simplePhpParser, TemplateFinder $templateFinder, NodePrinterInterface $nodePrinter, RemovedAndAddedFilesCollector $removedAndAddedFilesCollector, CommandArrayDecorator $commandArrayDecorator, CommandMethodDecorator $commandMethodDecorator)
    {
        $this->rectorParser = $rectorParser;
        $this->filesFinder = $filesFinder;
        $this->simplePhpParser = $simplePhpParser;
        $this->templateFinder = $templateFinder;
        $this->nodePrinter = $nodePrinter;
        $this->removedAndAddedFilesCollector = $removedAndAddedFilesCollector;
        $this->commandArrayDecorator = $commandArrayDecorator;
        $this->commandMethodDecorator = $commandMethodDecorator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\CommandController'))) {
            return null;
        }
        $commandClassMethods = $this->findCommandMethods($node);
        if ([] === $commandClassMethods) {
            return null;
        }
        if ([] === $node->namespacedName->parts) {
            return null;
        }
        // This is super hacky, but for now i have no other idea to test it here
        $currentSmartFileInfo = $this->file->getSmartFileInfo();
        $extEmConfFileInfo = $this->filesFinder->findExtEmConfRelativeFromGivenFileInfo($currentSmartFileInfo);
        if (!$extEmConfFileInfo instanceof SmartFileInfo) {
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
        foreach ($commandClassMethods as $commandMethod) {
            if (!$commandMethod instanceof ClassMethod) {
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
            $descriptionPhpDocNodes = $commandPhpDocInfo->getByType(PhpDocTextNode::class);
            $methodParameters = $commandMethod->params;
            if (!isset($descriptionPhpDocNodes[0])) {
                continue;
            }
            $commandDescription = $descriptionPhpDocNodes[0]->text;
            $commandTemplate = $this->templateFinder->getCommand();
            $commandName = Strings::firstUpper($commandMethodName);
            $commandContent = $commandTemplate->getContents();
            $filePath = \sprintf('%s/Classes/Command/%s.php', $extensionDirectory, $commandName);
            // Do not overwrite existing file
            if (\file_exists($filePath) && !StaticPHPUnitEnvironment::isPHPUnitRun()) {
                continue;
            }
            $commandVariables = $this->createCommandVariables($commandNamespace, $commandName, $commandDescription, $commandMethod);
            // Add traits, other methods etc. to class
            // Maybe inject dependencies into __constructor
            $commandContent = \str_replace(\array_keys($commandVariables), $commandVariables, $commandContent);
            $stmts = $this->simplePhpParser->parseString($commandContent);
            $inputArguments = $this->createInputArguments($methodParameters, $paramTags);
            $this->traverseNodesWithCallable($stmts, function (Node $node) use($inputArguments) {
                if (!$node instanceof ClassMethod) {
                    return null;
                }
                $this->commandMethodDecorator->decorate($node, $inputArguments);
            });
            $changedSetConfigContent = $this->nodePrinter->prettyPrintFile($stmts);
            $this->removedAndAddedFilesCollector->addAddedFile(new AddedFileWithContent($filePath, $changedSetConfigContent));
            $newCommandName = \sprintf('%s:%s', Strings::lower($vendorName), Strings::lower($commandName));
            $newCommandsWithFullQualifiedNamespace[$newCommandName] = \sprintf('%s\\%s', $commandNamespace, $commandName);
        }
        $this->addNewCommandsToCommandsFile($commandsFilePath, $newCommandsWithFullQualifiedNamespace);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate from extbase CommandController to Symfony Command', [new CodeSample(<<<'CODE_SAMPLE'
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
     * @return ClassMethod[]
     */
    private function findCommandMethods(Class_ $class) : array
    {
        return \array_filter($class->getMethods(), function (ClassMethod $classMethod) {
            if (!$classMethod->isPublic()) {
                return \false;
            }
            return $this->isName($classMethod->name, '*Command');
        });
    }
    /**
     * @param array<string, string> $newCommandsWithFullQualifiedNamespace
     */
    private function addNewCommandsToCommandsFile(string $commandsFilePath, array $newCommandsWithFullQualifiedNamespace) : void
    {
        if (\file_exists($commandsFilePath)) {
            $commandsSmartFileInfo = new SmartFileInfo($commandsFilePath);
            $stmts = $this->rectorParser->parseFile($commandsSmartFileInfo);
            $this->traverseNodesWithCallable($stmts, function (Node $node) use($newCommandsWithFullQualifiedNamespace) {
                if (!$node instanceof Array_) {
                    return null;
                }
                $this->commandArrayDecorator->decorateArray($node, $newCommandsWithFullQualifiedNamespace);
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            });
        } else {
            $array = new Array_();
            $this->commandArrayDecorator->decorateArray($array, $newCommandsWithFullQualifiedNamespace);
            $stmts = [new Return_($array)];
        }
        $changedCommandsContent = $this->nodePrinter->prettyPrintFile($stmts);
        $changedCommandsContent = Strings::replace($changedCommandsContent, self::REMOVE_EMPTY_LINES, '');
        $this->removedAndAddedFilesCollector->addAddedFile(new AddedFileWithContent($commandsFilePath, $changedCommandsContent));
    }
    /**
     * @param array<int, Node\Param> $methodParameters
     * @param ParamTagValueNode[] $paramTags
     * @return array<string, array{mode: int, name: string, description: string, default: mixed}>
     */
    private function createInputArguments(array $methodParameters, array $paramTags) : array
    {
        $inputArguments = [];
        foreach ($methodParameters as $key => $methodParameter) {
            $paramTag = $paramTags[$key] ?? null;
            $methodParamName = $this->nodeNameResolver->getName($methodParameter->var);
            if (null === $methodParamName) {
                continue;
            }
            $inputArguments[$methodParamName] = ['name' => $methodParamName, 'description' => null !== $paramTag ? $paramTag->description : '', 'mode' => null !== $methodParameter->default ? InputArgument::OPTIONAL : InputArgument::REQUIRED, 'default' => $methodParameter->default];
        }
        return $inputArguments;
    }
    /**
     * @return array<string, mixed>
     */
    private function createCommandVariables(string $commandNamespace, string $commandName, string $commandDescription, ClassMethod $commandMethod) : array
    {
        return ['__TEMPLATE_NAMESPACE__' => \ltrim($commandNamespace, '\\'), '__TEMPLATE_COMMAND_NAME__' => $commandName, '__TEMPLATE_DESCRIPTION__' => $commandDescription, '__TEMPLATE_COMMAND_BODY__' => $this->nodePrinter->prettyPrint((array) $commandMethod->stmts)];
    }
}
