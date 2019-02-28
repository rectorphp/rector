<?php declare(strict_types=1);

namespace Rector\Spaghetti\Rector;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Declare_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Foreach_;
use PhpParser\Node\Stmt\InlineHTML;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use Rector\Application\ErrorAndDiffCollector;
use Rector\Exception\NotImplementedException;
use Rector\Exception\ShouldNotHappenException;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Spaghetti\Controller\ControllerCallAndExtractFactory;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Strings\StringFormatConverter;

/**
 * This extract business logic to controller and returns array of values
 *
 * Works with @see RemoveLogicFromSpaghettiRector
 */
final class ExtractPhpFromSpaghettiRector extends AbstractFileSystemRector
{
    /**
     * @var StringFormatConverter
     */
    private $stringFormatConverter;

    /**
     * @var Expr[]
     */
    private $directlyEchoedNodes = [];

    /**
     * @var Stmt[]
     */
    private $rootNodesToRenderMethod = [];

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var Node[]
     */
    private $nodesContainingEcho = [];

    /**
     * @var ControllerCallAndExtractFactory
     */
    private $controllerCallAndExtractFactory;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    public function __construct(
        StringFormatConverter $stringFormatConverter,
        CallableNodeTraverser $callableNodeTraverser,
        ControllerCallAndExtractFactory $controllerCallAndExtractFactory,
        ErrorAndDiffCollector $errorAndDiffCollector
    ) {
        $this->stringFormatConverter = $stringFormatConverter;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->controllerCallAndExtractFactory = $controllerCallAndExtractFactory;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Take spaghetti template and separate it into 2 files: new class with render() method and variables + template only using the variables',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
<ul>
    <li><a href="<?php echo 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; ?>">Odkaz</a>
</ul>
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
<?php

class IndexController
{
    public function render()
    {
        return [
            'variable1' => 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']
        ];
    }
}

?>

-----

<?php

$variables = (new IndexController)->render();
extract($variables);

?>

<ul>
    <li><a href="<?php echo $variable1 ?>">Odkaz</a>
</ul>
CODE_SAMPLE
                ),
            ]
        );
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $originalFileContent = $smartFileInfo->getContents();

        $this->rootNodesToRenderMethod = [];
        $this->nodesContainingEcho = [];
        $this->directlyEchoedNodes = [];

        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        // analyze here! - collect variables
        $this->preProcessNodes($nodes);

        $classController = $this->createControllerClass($smartFileInfo);

        $nodesToPrepend = $this->controllerCallAndExtractFactory->create($classController);
        $nodesToPrepend = array_merge([$classController], [new Nop()], $nodesToPrepend);

        $fileContent = $this->completeAndPrintControllerRenderMethod($nodesToPrepend, $nodes);

        if ($fileContent === $originalFileContent) {
            // nothing to change
            return;
        }

        $this->filesystem->dumpFile($smartFileInfo->getRealPath(), $fileContent);

        $this->errorAndDiffCollector->addFileDiff($smartFileInfo, $fileContent, $originalFileContent);
    }

    /**
     * @todo extract to naming
     */
    private function createControllerName(SmartFileInfo $smartFileInfo): string
    {
        $camelCaseName = $this->stringFormatConverter->underscoreToCamelCase(
            $smartFileInfo->getBasenameWithoutSuffix()
        );

        return ucfirst($camelCaseName) . 'Controller';
    }

    private function createControllerClass(SmartFileInfo $smartFileInfo): Class_
    {
        $controllerName = $this->createControllerName($smartFileInfo);

        $classController = new Class_(new Identifier($controllerName));
        $classController->stmts[] = $this->createControllerRenderMethod();

        return $classController;
    }

    private function createControllerRenderMethod(): ClassMethod
    {
        $renderMethod = $this->nodeFactory->createPublicMethod('render');
        $renderMethod->stmts = [];

        if ($this->rootNodesToRenderMethod) {
            foreach ($this->rootNodesToRenderMethod as $key => $nodeToRenderMethod) {
                // make any changes in them permanent, e.g. foreach with value change → re-assign value "$values[$key] = $value;"
                if (in_array($nodeToRenderMethod, $this->nodesContainingEcho, true)) {
                    $this->rootNodesToRenderMethod[$key] = $this->makeChangePersistent($nodeToRenderMethod);
                }
            }

            $renderMethod->stmts = $this->rootNodesToRenderMethod;
        }

        $array = $this->createTemplateVariablesArray();
        $renderMethod->stmts[] = new Return_($array);

        return $renderMethod;
    }

    /**
     * Save echoed value to it's key in foreach instead etc.
     */
    private function makeChangePersistent(Stmt $stmt): Stmt
    {
        /** @var Stmt[] $nodes */
        $nodes = $this->callableNodeTraverser->traverseNodesWithCallable([$stmt], function (Node $stmt) {
            if ($stmt instanceof Assign) {
                $this->print($stmt);
            }

            // complete assign to foreach if not recorded yet
            if ($stmt instanceof Foreach_) { // @todo or anything with "stmts" property
                // recreate
                $foreach = $stmt;
                foreach ($foreach->stmts as $key => $foreachStmt) {
                    $foreachStmt = $foreachStmt instanceof Expression ? $foreachStmt->expr : $foreachStmt;

                    if ($foreachStmt instanceof Echo_) {
                        // silence echoes
                        unset($foreach->stmts[$key]);
                        return $foreach;
                    }

                    $newForeach = new Foreach_(clone $foreach->expr, clone $foreach->valueVar);

                    if ($foreachStmt instanceof AssignOp) {
                        if (! $this->areNodesEqual($foreach->valueVar, $foreachStmt->var)) {
                            return $foreach;
                        }

                        if (! $this->areNodesEqual($foreach->valueVar, $foreachStmt->var)) {
                            return $stmt;
                        }

                        $assign = $this->createForeachKeyAssign($newForeach, $foreachStmt->var);
                        $newForeach->stmts[] = new Expression($foreachStmt);
                        $newForeach->stmts[] = new Expression($assign);
                    }

                    return $newForeach;
                }
            }

            return $stmt;
        });

        // only 1 node passed → 1 node is returned
        if (! isset($nodes[0])) {
            throw new ShouldNotHappenException();
        }

        return $nodes[0];
    }

    private function createForeachKeyAssign(Foreach_ $foreach, Expr $expr): Assign
    {
        if ($foreach->keyVar === null) {
            $foreach->keyVar = new Variable('key'); // @todo check for duplicit name in nested foreaches
        }

        $keyVariable = $foreach->keyVar;

        $arrayDimFetch = new ArrayDimFetch($foreach->expr, $keyVariable);

        return new Assign($arrayDimFetch, $expr);
    }

    /**
     * @param Node[] $nodes
     */
    private function preProcessNodes(array $nodes): void
    {
        $i = 0;
        foreach ($nodes as $key => $node) {
            if ($node instanceof InlineHTML) {
                // @todo are we in a for/foreach?
                continue;
            }

            if ($node instanceof Echo_) {
                if (count($node->exprs) === 1) {
                    // is it already a variable? nothing to change
                    if ($node->exprs[0] instanceof Variable) {
                        continue;
                    }

                    ++$i;

                    $variableName = 'variable' . $i;
                    $this->directlyEchoedNodes[$variableName] = $node->exprs[0];

                    $node->exprs[0] = new Variable($variableName);
                }
            } else {
                // expression assign variable!?
                if ($this->isNodeEchoedAnywhereInside($node)) {
                    $this->rootNodesToRenderMethod[] = $node;
                    $this->nodesContainingEcho[] = $node;
                } else {
                    // nodes to skip
                    if ($node instanceof Declare_) {
                        unset($nodes[$key]);
                        continue;
                    }

                    // just copy
                    $this->rootNodesToRenderMethod[] = $node;
                    // remove node
                    unset($nodes[$key]);
                    continue;
                }
            }
        }
    }

    /**
     * Creates: "return ['value' => $value]"
     */
    private function createTemplateVariablesArray(): Array_
    {
        $array = new Array_();

        foreach ($this->directlyEchoedNodes as $name => $expr) {
            $array->items[] = new ArrayItem($expr, new String_($name));
        }

        foreach ($this->nodesContainingEcho as $nodeContainingEcho) {
            // @todo return exactly the one variable needed in template
            if ($nodeContainingEcho instanceof Foreach_) {
                if ($nodeContainingEcho->expr instanceof Variable) {
                    $variableName = $this->getName($nodeContainingEcho->expr);
                    if ($variableName === null) {
                        throw new NotImplementedException(sprintf(
                            'Method "%s" is not implemented yet for "%s" node',
                            __METHOD__,
                            get_class($nodeContainingEcho->expr)
                        ));
                    }

                    $array->items[] = new ArrayItem($nodeContainingEcho->expr, new String_($variableName));

                    continue;
                }
            }

            // @todo
            throw new NotImplementedException(sprintf(
                'Method "%s" is not implemented yet for "%s" node',
                __METHOD__,
                get_class($nodeContainingEcho)
            ));
        }

        return $array;
    }

    /**
     * @param Node[] $nodesToPrepend
     * @param Node[] $nodes
     */
    private function completeAndPrintControllerRenderMethod(array $nodesToPrepend, array $nodes): string
    {
        // @todo turn nodes to template ones - how to remove a node here?
        $nodesToRemove = [];
        $this->callableNodeTraverser->traverseNodesWithCallable($nodes, function (Node $node) use (&$nodesToRemove) {
            if ($node instanceof AssignOp) {
                $nodesToRemove[] = $node;
            }

            if ($node instanceof Declare_) {
                $nodesToRemove[] = $node;
            }

            if ($node instanceof Assign) {
                $nodesToRemove[] = $node;
            }

            return $node;
        });

        $nodes = $this->removeNodesFromNodes($nodesToRemove, $nodes);

        // print template file
        $fileContent = sprintf(
            '<?php%s%s%s?>%s%s',
            PHP_EOL,
            $this->print($nodesToPrepend),
            PHP_EOL . PHP_EOL,
            PHP_EOL,
            $this->printNodesToString($nodes) // to many old nodes?
        );

        // remove "? >...< ?php" leftovers
        return Strings::replace($fileContent, '#\s{1,2}\?\>(\s+)\<\?php#s');
    }

    private function isNodeEchoedAnywhereInside(Node $node): bool
    {
        return $this->betterNodeFinder->containsInstanceOf($node, Echo_::class);
    }
}
