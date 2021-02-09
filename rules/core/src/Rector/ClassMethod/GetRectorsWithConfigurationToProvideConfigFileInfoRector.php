<?php

declare(strict_types=1);

namespace Rector\Core\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ReturnTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithContent;
use Symplify\PhpConfigPrinter\PhpParser\NodeFactory\ConfiguratorClosureNodeFactory;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SymfonyPhpConfig\ValueObjectInliner;

/**
 * @see \Rector\Core\Tests\Rector\ClassMethod\GetRectorsWithConfigurationToProvideConfigFileInfoRector\GetRectorsWithConfigurationToProvideConfigFileInfoRectorTest
 */
final class GetRectorsWithConfigurationToProvideConfigFileInfoRector extends AbstractRector
{
    /**
     * @var ConfiguratorClosureNodeFactory
     */
    private $configuratorClosureNodeFactory;

    public function __construct(ConfiguratorClosureNodeFactory $configuratorClosureNodeFactory)
    {
        $this->configuratorClosureNodeFactory = $configuratorClosureNodeFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused parameter in constructor', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            NewArgToMethodCallRector::class => [
                NewArgToMethodCallRector::NEW_ARGS_TO_METHOD_CALLS => [
                    new NewArgToMethodCall(SomeDotenv::class, true, 'usePutenv'),
                ],
            ],
        ];
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    protected function getRectorsWithConfiguration(): \Symplify\SmartFileSystem\SmartFileInfo
    {
        return new \Symplify\SmartFileSystem\SmartFileInfo(__DIR__ . '/config/configured_rule.php');
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, 'getRectorsWithConfiguration')) {
            return null;
        }

        $node->name = new Node\Identifier('provideConfigFileInfo');

        /** @var Return_ $oldReturn */
        $oldReturn = $node->stmts[0];

        $node->returnType = new Node\NullableType(new FullyQualified(SmartFileInfo::class));
        $this->createReturnTag($node);

        /** @var SmartFileInfo $smartFileInfo */
        $smartFileInfo = $node->getAttribute(SmartFileInfo::class);
        $realPath = $smartFileInfo->getRealPath();
        $configFilePath = dirname($realPath) . '/config/configured_rule.php';

        $services = [];

        $services[] = new Node\Stmt\Expression(new Node\Expr\Assign(new Variable('services'), new MethodCall(
            new Variable(
            'containerConfigurator'
        ),
            'services'
        )));

        $closure = null;
        $serviceVariable = new Variable('services');

        // get config value
        /** @var Array_ $returnedArray */
        $returnedArray = $oldReturn->expr;
        foreach ($returnedArray->items as $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            if ($arrayItem->key instanceof ClassConstFetch) {
                $methodCall = new MethodCall($serviceVariable, 'set', [$arrayItem->key]);
                if ($arrayItem->value instanceof Array_) {
                    if ($arrayItem->value->items === []) {
                        $services[] = new Node\Stmt\Expression($methodCall);
                    } else {
                        foreach ($arrayItem->value->items as $nestedArrayItem) {
                            if (! $nestedArrayItem instanceof ArrayItem) {
                                continue;
                            }

                            if ($nestedArrayItem->key instanceof ClassConstFetch) {
                                $hasValueObjects = $this->hasValueObjects($nestedArrayItem);

                                if ($hasValueObjects) {
                                    $configurationArray = new Node\Expr\StaticCall(new FullyQualified(
                                        ValueObjectInliner::class
                                    ), 'inline', [
                                        $nestedArrayItem->value,
                                    ]);
                                } else {
                                    $configurationArray = $nestedArrayItem->value;
                                }

                                $arrayConfiguration = new Array_([new ArrayItem(
                                    new Array_([new ArrayItem($configurationArray, $nestedArrayItem->key)])),
                                ]);

                                $methodCall = new MethodCall($methodCall, 'call', [
                                    new Node\Scalar\String_('configure'),
                                    $arrayConfiguration,
                                ]);
                            }

                            $services[] = new Node\Stmt\Expression($methodCall);
                        }
                    }
                }

                $closure = $this->configuratorClosureNodeFactory->createContainerClosureFromStmts($services);
            }
        }

        if ($closure === null) {
            throw new ShouldNotHappenException();
        }

        $return = new Return_($closure);
        $fileContent = $this->betterStandardPrinter->prettyPrintFile([$return]);

        dump($fileContent);

        $this->createPhpConfigFileAndDumpToPath($fileContent, $configFilePath);

        $node->stmts = [$this->createReturn()];

        return $node;
    }

    private function createReturn(): Return_
    {
        $new = new New_(new FullyQualified(SmartFileInfo::class));
        $new->args[] = new Node\Expr\BinaryOp\Concat(new Node\Scalar\MagicConst\Dir(), new Node\Scalar\String_(
            '/config/configured_rule.php'
        ));

        return new Return_($new);
    }

    private function createReturnTag($node): void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($node);
        $phpDocInfo->removeByType(ReturnTagValueNode::class);
    }

    private function createPhpConfigFileAndDumpToPath(string $fileContent, string $filePath): void
    {
        $addedFileWithContent = new AddedFileWithContent($filePath, $fileContent);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithContent);
    }

    private function hasValueObjects(ArrayItem $nestedArrayItem): bool
    {
        if ($nestedArrayItem->value instanceof Array_) {
            foreach ($nestedArrayItem->value->items as $nestedNestedArrayItem) {
                if (! $nestedNestedArrayItem instanceof ArrayItem) {
                    continue;
                }

                return $nestedNestedArrayItem->value instanceof New_;
            }
        }

        return false;
    }
}
