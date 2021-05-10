<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\FileWithoutNamespace;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\FileSystemRector\ValueObject\AddedFileWithNodes;
use Rector\Transform\Naming\FullyQualifiedNameResolver;
use Rector\Transform\NodeFactory\StaticMethodClassFactory;
use Rector\Transform\ValueObject\FunctionToStaticCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;
/**
 * @see \Rector\Tests\Transform\Rector\FileWithoutNamespace\FunctionToStaticMethodRector\FunctionToStaticMethodRectorTest
 */
final class FunctionToStaticMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\CodingStyle\Naming\ClassNaming
     */
    private $classNaming;
    /**
     * @var \Rector\Transform\NodeFactory\StaticMethodClassFactory
     */
    private $staticMethodClassFactory;
    /**
     * @var \Rector\Transform\Naming\FullyQualifiedNameResolver
     */
    private $fullyQualifiedNameResolver;
    public function __construct(\Rector\CodingStyle\Naming\ClassNaming $classNaming, \Rector\Transform\NodeFactory\StaticMethodClassFactory $staticMethodClassFactory, \Rector\Transform\Naming\FullyQualifiedNameResolver $fullyQualifiedNameResolver)
    {
        $this->classNaming = $classNaming;
        $this->staticMethodClassFactory = $staticMethodClassFactory;
        $this->fullyQualifiedNameResolver = $fullyQualifiedNameResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change functions to static calls, so composer can autoload them', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
function some_function()
{
}

some_function('lol');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeUtilsClass
{
    public static function someFunction()
    {
    }
}

SomeUtilsClass::someFunction('lol');
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace::class, \PhpParser\Node\Stmt\Namespace_::class];
    }
    /**
     * @param FileWithoutNamespace|Namespace_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        /** @var Function_[] $functions */
        $functions = $this->betterNodeFinder->findInstanceOf($node, \PhpParser\Node\Stmt\Function_::class);
        if ($functions === []) {
            return null;
        }
        $smartFileInfo = $this->file->getSmartFileInfo();
        $shortClassName = $this->classNaming->getNameFromFileInfo($smartFileInfo);
        $class = $this->staticMethodClassFactory->createStaticMethodClass($shortClassName, $functions);
        $stmts = $node->stmts;
        $this->removeNodes($functions);
        // replace function calls with class static call
        $functionsToStaticCalls = $this->resolveFunctionsToStaticCalls($stmts, $shortClassName, $functions);
        $node->stmts = $this->replaceFuncCallsWithStaticCalls($stmts, $functionsToStaticCalls);
        $this->printStaticMethodClass($smartFileInfo, $shortClassName, $node, $class);
        return $node;
    }
    /**
     * @param Node[] $stmts
     * @param Function_[] $functions
     * @return FunctionToStaticCall[]
     */
    private function resolveFunctionsToStaticCalls(array $stmts, string $shortClassName, array $functions) : array
    {
        $functionsToStaticCalls = [];
        $className = $this->fullyQualifiedNameResolver->resolveFullyQualifiedName($stmts, $shortClassName);
        foreach ($functions as $function) {
            $functionName = $this->getName($function);
            if ($functionName === null) {
                continue;
            }
            $methodName = $this->classNaming->createMethodNameFromFunction($function);
            $functionsToStaticCalls[] = new \Rector\Transform\ValueObject\FunctionToStaticCall($functionName, $className, $methodName);
        }
        return $functionsToStaticCalls;
    }
    /**
     * @param Node[] $stmts
     * @param FunctionToStaticCall[] $functionsToStaticCalls
     * @return Node[]
     */
    private function replaceFuncCallsWithStaticCalls(array $stmts, array $functionsToStaticCalls) : array
    {
        $this->traverseNodesWithCallable($stmts, function (\PhpParser\Node $node) use($functionsToStaticCalls) : ?StaticCall {
            if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
                return null;
            }
            foreach ($functionsToStaticCalls as $functionToStaticCall) {
                if (!$this->isName($node, $functionToStaticCall->getFunction())) {
                    continue;
                }
                $staticCall = $this->nodeFactory->createStaticCall($functionToStaticCall->getClass(), $functionToStaticCall->getMethod());
                $staticCall->args = $node->args;
                return $staticCall;
            }
            return null;
        });
        return $stmts;
    }
    /**
     * @param Namespace_|FileWithoutNamespace $node
     */
    private function printStaticMethodClass(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo, string $shortClassName, \PhpParser\Node $node, \PhpParser\Node\Stmt\Class_ $class) : void
    {
        $classFileDestination = $smartFileInfo->getPath() . \DIRECTORY_SEPARATOR . $shortClassName . '.php';
        $nodesToPrint = [$this->resolveNodeToPrint($node, $class)];
        $addedFileWithNodes = new \Rector\FileSystemRector\ValueObject\AddedFileWithNodes($classFileDestination, $nodesToPrint);
        $this->removedAndAddedFilesCollector->addAddedFile($addedFileWithNodes);
    }
    /**
     * @param Namespace_|FileWithoutNamespace $node
     * @return Namespace_|Class_
     */
    private function resolveNodeToPrint(\PhpParser\Node $node, \PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Stmt
    {
        if ($node instanceof \PhpParser\Node\Stmt\Namespace_) {
            return new \PhpParser\Node\Stmt\Namespace_($node->name, [$class]);
        }
        return $class;
    }
}
