<?php

declare(strict_types=1);

namespace Rector\Core\Console\Command;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\TypeUtils;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\FileSystemRector\Parser\FileInfoParser;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\StaticTypeMapper\StaticTypeMapper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Console\Command\CommandNaming;
use Symplify\PackageBuilder\Console\ShellCode;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ScreenFileCommand extends AbstractCommand
{
    /**
     * @var string
     */
    private const FILE_ARGUMENT = 'file';

    /**
     * @var string
     */
    private const NAME = 'name';

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
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct(
        BetterStandardPrinter $betterStandardPrinter,
        CallableNodeTraverser $callableNodeTraverser,
        FileInfoParser $fileInfoParser,
        NodeNameResolver $nodeNameResolver,
        NodeTypeResolver $nodeTypeResolver,
        SmartFileSystem $smartFileSystem,
        StaticTypeMapper $staticTypeMapper,
        SymfonyStyle $symfonyStyle
    ) {
        $this->symfonyStyle = $symfonyStyle;
        $this->fileInfoParser = $fileInfoParser;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->betterStandardPrinter = $betterStandardPrinter;
        $this->staticTypeMapper = $staticTypeMapper;

        parent::__construct();

        $this->smartFileSystem = $smartFileSystem;
    }

    protected function configure(): void
    {
        $this->setName(CommandNaming::classToName(self::class));
        $this->setDescription('[DEV] Load file and print nodes meta data - super helpful to learn to build rules');

        $this->addArgument(self::FILE_ARGUMENT, InputArgument::REQUIRED, 'Path to file to be screened');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // 1. load file

        /** @var string $filePath */
        $filePath = $input->getArgument(self::FILE_ARGUMENT);
        $smartFileInfo = new SmartFileInfo($filePath);

        // 2. parse file to nodes
        $nodes = $this->fileInfoParser->parseFileInfoToNodesAndDecorateWithScope($smartFileInfo);

        // 3. decorate nodes
        $this->decorateNodes($nodes);

        // 4. print decorated nodes to output/file
        $this->outputDecoratedFileContent($nodes, $smartFileInfo);

        return ShellCode::SUCCESS;
    }

    /**
     * @param Node[] $nodes
     */
    private function decorateNodes(array $nodes): void
    {
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node): Node {
            $infoNode = $node instanceof Expression ? $node->expr : $node;

            $data = $this->decorateNodeData($infoNode);

            $docBlock = $this->createDocBlockFromArrayData($data);
            if ($node->getDocComment() === null) {
                $node->setDocComment(new Doc($docBlock));
            } else {
                // join with previous doc
                $previousText = $node->getDocComment()->getText();
                $newDocBlock = $previousText . $docBlock;
                $node->setDocComment(new Doc($newDocBlock));
            }

            return $node;
        });
    }

    /**
     * @param Node[] $nodes
     */
    private function outputDecoratedFileContent(array $nodes, SmartFileInfo $fileInfo): void
    {
        $outputFileName = 'rector_vision_' . $fileInfo->getFilename();
        $decoratedFileContent = $this->betterStandardPrinter->prettyPrintFile($nodes);

        $this->smartFileSystem->dumpFile($outputFileName, $decoratedFileContent);
        $message = sprintf('See: %s', $outputFileName);

        $this->symfonyStyle->writeln($message);
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function decorateNodeData(Node $node, array $data = []): array
    {
        $data = $this->decorateWithNodeType($node, $data);

        if ($node instanceof ClassLike) {
            $data = $this->decorateClassLike($node, $data);
        }

        if ($node instanceof Assign) {
            $data = $this->decorateAssign($node, $data);
        }

        $data = $this->addNameData($node, $data);
        $data = $this->addVariableTypeData($node, $data);

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
    private function decorateClassLike(ClassLike $classLike, array $data): array
    {
        $data[self::NAME] = $this->nodeNameResolver->getName($classLike);

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
    private function decorateAssign(Assign $assign, array $data): array
    {
        $data['assign_var (the left one)'] = $this->decorateNodeData($assign->var);
        $data['assign_expr (the right one)'] = $this->decorateNodeData($assign->expr);

        return $data;
    }

    /**
     * @return mixed[]
     */
    private function addNameData(Node $node, array $data): array
    {
        if ($node instanceof Variable) {
            $data[self::NAME] = $this->nodeNameResolver->getName($node);
        }

        if ($node instanceof Namespace_ && $node->name !== null) {
            $data[self::NAME] = $this->nodeNameResolver->getName($node->name);
        }

        if ($node instanceof FuncCall && $node->name !== null) {
            $data[self::NAME] = $this->nodeNameResolver->getName($node->name);
        }

        return $data;
    }

    /**
     * @return mixed[]
     */
    private function addVariableTypeData(Node $node, array $data): array
    {
        if ($node instanceof Variable) {
            $staticType = $this->nodeTypeResolver->getStaticType($node);

            $classNames = TypeUtils::getDirectClassNames($staticType);
            if ($classNames !== []) {
                $objectTypesAsString = implode(', ', $classNames);
                $data['variable_types'] = $objectTypesAsString;
            } else {
                $typeString = $this->staticTypeMapper->mapPHPStanTypeToDocString($staticType);
                $data['variable_types'] = $typeString;
            }
        }
        return $data;
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function decorateReturn(Return_ $return, array $data): array
    {
        if ($return->expr === null) {
            return $data;
        }

        $data['returned_node'] = $this->decorateNodeData($return->expr);

        return $data;
    }


    private function getObjectShortClass(object $object): string
    {
        $classNode = get_class($object);

        return (string) Strings::after($classNode, '\\', -1);
    }

    /**
     * @param mixed[] $data
     * @return mixed[]
     */
    private function decorateMethodCall(MethodCall $methodCall, array $data): array
    {
        $data['method_call_variable'] = $this->decorateNodeData($methodCall->var);
        $data['method_call_name'] = $this->nodeNameResolver->getName($methodCall->name);

        return $data;
    }
}
