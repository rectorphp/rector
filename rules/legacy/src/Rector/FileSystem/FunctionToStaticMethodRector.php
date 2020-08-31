<?php

declare(strict_types=1);

namespace Rector\Legacy\Rector\FileSystem;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Autodiscovery\ValueObject\NodesWithFileDestination;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\PhpParser\Builder\ClassBuilder;
use Rector\Core\PhpParser\Builder\MethodBuilder;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Rector\Legacy\ValueObject\StaticCallPointer;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\Legacy\Tests\Rector\FileSystem\FunctionToStaticMethodRector\FunctionToStaticMethodRectorTest
 */
final class FunctionToStaticMethodRector extends AbstractFileSystemRector
{
    /**
     * @var StaticCallPointer[]
     */
    private $functionNameToClassStaticMethod = [];

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(ClassNaming $classNaming)
    {
        $this->classNaming = $classNaming;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change functions to static calls, so composer can autoload them', [
            new CodeSample(
                <<<'PHP'
function some_function()
{
}

some_function('lol');
PHP
                ,
                <<<'PHP'
class SomeUtilsClass
{
    public static function someFunction()
    {
    }
}

SomeUtilsClass::someFunction('lol');
PHP
            ),
        ]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        $nodes = $this->parseFileInfoToNodes($smartFileInfo);
        $fileStmts = $this->getFileOrNamespaceStmts($nodes);

        /** @var Function_[] $functions */
        $functions = $this->betterNodeFinder->findInstanceOf($fileStmts, Function_::class);
        if ($functions === []) {
            return;
        }

        $shortClassName = $this->classNaming->getNameFromFileInfo($smartFileInfo);
        $classBuilder = new ClassBuilder($shortClassName);
        $classBuilder->makeFinal();

        $className = $this->getFullyQualifiedName($nodes, $shortClassName);

        foreach ($functions as $function) {
            $functionName = $this->getName($function);
            $methodName = $this->classNaming->createMethodNameFromFunction($function);
            $this->functionNameToClassStaticMethod[$functionName] = new StaticCallPointer($className, $methodName);

            $staticClassMethod = $this->createClassMethodFromFunction($methodName, $function);
            $classBuilder->addStmt($staticClassMethod);

            // remove after convert, we won't need it
            $this->removeNode($function);
        }

        $class = $classBuilder->getNode();

        $classFilePath = $smartFileInfo->getPath() . DIRECTORY_SEPARATOR . $shortClassName . '.php';
        $nodesToPrint = $this->resolveNodesToPrint($nodes, $class);

        // replace function calls with class static call

        $this->traverseNodesWithCallable($nodes, function (Node $node): ?StaticCall {
            if (! $node instanceof FuncCall) {
                return null;
            }

            $funcCallName = $this->getName($node);
            $staticCallPointer = $this->functionNameToClassStaticMethod[$funcCallName] ?? null;
            if ($staticCallPointer === null) {
                return null;
            }

            $staticCall = $this->createStaticCall($staticCallPointer->getClass(), $staticCallPointer->getMethod());
            $staticCall->args = $node->args;

            return $staticCall;
        });

        // @todo decouple to PostRectorInterface, so it's covered in external files too
        $nodesWithFileDestination = new NodesWithFileDestination($nodesToPrint, $classFilePath, $smartFileInfo);
        $this->printNodesWithFileDestination($nodesWithFileDestination);
    }

    /**
     * @param Node[] $nodes
     * @return Node[]|Stmt[]
     */
    private function getFileOrNamespaceStmts(array $nodes): array
    {
        /** @var Namespace_|null $namespace */
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if ($namespace === null) {
            return $nodes;
        }

        return $namespace->stmts;
    }

    private function getFullyQualifiedName(array $nodes, string $shortClassName): string
    {
        /** @var Namespace_|null $namespace */
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if ($namespace === null) {
            return $shortClassName;
        }

        $namespaceName = $this->getName($namespace);
        if ($namespaceName === null) {
            return $shortClassName;
        }

        return $namespaceName . '\\' . $shortClassName;
    }

    private function createClassMethodFromFunction(string $methodName, Function_ $function): ClassMethod
    {
        $methodBuilder = new MethodBuilder($methodName);
        $methodBuilder->makePublic();
        $methodBuilder->makeStatic();
        $methodBuilder->addStmts($function->stmts);

        return $methodBuilder->getNode();
    }

    /**
     * @param Node[] $nodes
     * @return Namespace_[]|Class_[]
     */
    private function resolveNodesToPrint(array $nodes, Class_ $class): array
    {
        /** @var Namespace_|null $namespace */
        $namespace = $this->betterNodeFinder->findFirstInstanceOf($nodes, Namespace_::class);
        if ($namespace !== null) {
            // put class first
            $namespace->stmts = array_merge([$class], $namespace->stmts);

            return [$namespace];
        }

        return [$class];
    }
}
