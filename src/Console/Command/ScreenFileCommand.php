<?php declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Utils\FileSystem;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Nop;
use PHPStan\Type\ObjectType;
use PHPStan\Type\UnionType;
use Rector\Console\Shell;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Commander\NodeAddingCommander;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\PhpParser\Printer\BetterStandardPrinter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class ScreenFileCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private const FILE_ARGUMENT = 'file';

    /**
     * @var string
     */
    private const OUTPUT_OPTION = 'output';

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var FileInfoParser
     */
    private $fileInfoParser;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var NodeAddingCommander
     */
    private $nodeAddingCommander;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        BetterStandardPrinter $betterStandardPrinter,
        FileInfoParser $fileInfoParser,
        CallableNodeTraverser $callableNodeTraverser,
        NameResolver $nameResolver,
        NodeAddingCommander $nodeAddingCommander,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->fileInfoParser = $fileInfoParser;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nameResolver = $nameResolver;
        $this->nodeAddingCommander = $nodeAddingCommander;
        $this->nodeTypeResolver = $nodeTypeResolver;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('Load file and print with meta data about nodes - super helpful to learn to build rules');

        $this->addArgument(self::FILE_ARGUMENT, InputArgument::OPTIONAL, 'Path to file to be screened');
        $this->addOption(self::OUTPUT_OPTION, 'o', InputOption::VALUE_REQUIRED, 'Path to print decorated file into');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $filePath = $input->getArgument(self::FILE_ARGUMENT);
        $smartFileInfo = new SmartFileInfo($filePath);

        include_once $filePath;

        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorate($smartFileInfo);

        $this->decorateNodes($nodes);

        $decoratedFileContent = '<?php' . PHP_EOL . $this->printNodesToString($nodes);

        $outputOption = (string) $input->getOption(self::OUTPUT_OPTION);
        if ($outputOption) {
            FileSystem::write($outputOption, $decoratedFileContent);
        } else {
            $this->symfonyStyle->writeln($decoratedFileContent);
        }

        return Shell::CODE_SUCCESS;
    }

    /**
     * @param mixed[] $data
     */
    private function createDocBlockFromArrayData(array $data): string
    {
        $comments = '';
        $comments .= PHP_EOL;

        foreach ($data as $name => $value) {
            $comments .= sprintf('// %s: "%s"', $name, $value) . PHP_EOL;
        }

        return $comments;
    }

    /**
     * @param Node[] $nodes
     */
    private function printNodesToString(array $nodes): string
    {
        return $this->betterStandardPrinter->prettyPrint($nodes);
    }

    /**
     * @param Node[] $nodes
     */
    private function decorateNodes(array $nodes): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node): Node {
            // not useful
            if ($node instanceof Node\Stmt\Expression) {
                $infoNode = $node->expr;
            } else {
                $infoNode = $node;
            }

            $data = [];
            if ($infoNode instanceof ClassLike) {
                $data['class_name'] = $this->nameResolver->getName($infoNode);
            }

            if ($infoNode instanceof Variable) {
                $data['variable_name - $this->isName($node, "X")'] = $this->nameResolver->getName($infoNode);
            }

            if ($infoNode instanceof Node\Expr\MethodCall) {
                $data['method_variable_name - $this->isName($node->var, "X")'] = $this->nameResolver->getName(
                    $infoNode->var
                );
                $data['method_call_method_name - $this->isName($node->name, "X")'] = $this->nameResolver->getName(
                    $infoNode->name
                );

                $objectType = $this->nodeTypeResolver->getStaticType($infoNode->var);
                if ($objectType instanceof ObjectType) {
                    $data['method_variable_type - $this->isObjectType($node, "X")'] = $objectType->getClassName();
                } elseif ($objectType instanceof UnionType) {
                    $objectTypes = [];

                    // @todo solve that later
                    foreach ($objectType->getTypes() as $unionedObjectType) {
                        if ($unionedObjectType instanceof ObjectType) {
                            $objectTypes[] = $unionedObjectType;
                        }
                    }

                    $objectTypesAsString = implode(', ', $objectTypes);
                    $data['method_variable_types - $this->isObjectType($node, "X")'] = $objectTypesAsString;
                }
            }

            $data['node_type'] = get_class($infoNode);

            $docBlock = $this->createDocBlockFromArrayData($data);
            if ($node->getDocComment() === null) {
                $node->setDocComment(new Doc($docBlock));
            } else {
                // join with previous doc
                $previousDoc = $node->getDocComment()->getText();
                $newDocBlock = $previousDoc . $docBlock;
                $node->setDocComment(new Doc($newDocBlock));
            }

            // make extra space between each node for readability
            $nopNode = new Nop();
            $this->nodeAddingCommander->addNodeBeforeNode($nopNode, $node);

            return $node;
        });
    }
}
