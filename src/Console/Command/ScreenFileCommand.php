<?php

declare(strict_types=1);

namespace Rector\Console\Command;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use PHPStan\Type\UnionType;
use Rector\Console\Shell;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
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
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    public function __construct(
        SymfonyStyle $symfonyStyle,
        FileInfoParser $fileInfoParser,
        CallableNodeTraverser $callableNodeTraverser,
        NameResolver $nameResolver,
        NodeTypeResolver $nodeTypeResolver,
        BetterStandardPrinter $betterStandardPrinter
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->fileInfoParser = $fileInfoParser;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nameResolver = $nameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;

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

        require_once $smartFileInfo->getRealPath();

        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorateWithScope($smartFileInfo);

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
    private function createDocBlockFromArrayData(array $data, string $indent = ''): string
    {
        $comments = '';
        $comments .= PHP_EOL;

        foreach ($data as $name => $value) {
            $wrapInQuotes = true;
            if (is_array($value)) {
                $wrapInQuotes = false;
                $value = $this->createDocBlockFromArrayData($value, '  * ');
            }

            $comments .= sprintf(
                '// %s%s: %s%s%s',
                $indent,
                $name,
                $wrapInQuotes ? '"' : '',
                $value,
                $wrapInQuotes ? '"' : ''
            ) . PHP_EOL;
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
            if ($node instanceof Expression) {
                $infoNode = $node->expr;
            } else {
                $infoNode = $node;
            }

            $data = [];
            $data = $this->decorateNodeData($infoNode, $data);

            $docBlock = $this->createDocBlockFromArrayData($data);
            if ($node->getDocComment() === null) {
                $node->setDocComment(new Doc($docBlock));
            } else {
                // join with previous doc
                $previousDoc = $node->getDocComment()->getText();
                $newDocBlock = $previousDoc . $docBlock;
                $node->setDocComment(new Doc($newDocBlock));
            }

            return $node;
        });
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function decorateClassLike(ClassLike $classLike, array $data): array
    {
        $data['class_name'] = $this->nameResolver->getName($classLike);

        $parentClassName = $classLike->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName) {
            $data['parent_class_name'] = $parentClassName;
        }

        return $data;
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function decorateMethodCall(MethodCall $methodCall, array $data): array
    {
        $data['method_variable_name'] = $this->nameResolver->getName($methodCall->var);

        $data['method_call_method_name'] = $this->nameResolver->getName($methodCall->name);

        $staticType = $this->nodeTypeResolver->getStaticType($methodCall->var);

        if ($staticType instanceof TypeWithClassName) {
            $data['method_variable_type'] = $staticType->getClassName();
        } elseif ($staticType instanceof UnionType) {
            $objectTypes = [];

            foreach ($staticType->getTypes() as $unionedObjectType) {
                if ($unionedObjectType instanceof ObjectType) {
                    $objectTypes[] = $unionedObjectType;
                }
            }

            $objectTypesAsString = implode(', ', $objectTypes);
            $data['method_variable_types'] = $objectTypesAsString;
        }

        return $data;
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function decorateWithNodeType(Node $node, array $data): array
    {
        $data['node_type'] = $this->getObjectShortClass($node);

        return $data;
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function decorateReturn(Return_ $return, array $data): array
    {
        $data['returned_node'] = [];
        $data['returned_node'] = $this->decorateNodeData($return->expr, $data['returned_node']);

        return $data;
    }

    /**
     * @param object $object
     */
    private function getObjectShortClass($object): string
    {
        $classNode = get_class($object);
        return Strings::after($classNode, '\\', -1);
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function decorateNodeData(Node $node, array $data): array
    {
        $data = $this->decorateWithNodeType($node, $data);

        if ($node instanceof ClassLike) {
            $data = $this->decorateClassLike($node, $data);
        }

        if ($node instanceof Variable) {
            $data['variable_name'] = $this->nameResolver->getName($node);
        }

        if ($node instanceof Namespace_) {
            $data['name'] = $this->nameResolver->getName($node->name);
        }

        if ($node instanceof FuncCall) {
            $data['name'] = $this->nameResolver->getName($node->name);
        }

        if ($node instanceof Return_) {
            $data = $this->decorateReturn($node, $data);
        }

        if ($node instanceof ArrayItem) {
            $arrayItemValueStaticType = $this->nodeTypeResolver->getStaticType($node->value);

            $shortStaticType = $this->getObjectShortClass($arrayItemValueStaticType);
            $data['value_static_type'] = $shortStaticType;
        }

        if ($node instanceof MethodCall) {
            $data = $this->decorateMethodCall($node, $data);
        }
        return $data;
    }
}
