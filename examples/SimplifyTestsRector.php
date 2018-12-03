<?php declare(strict_types=1);

namespace Rector\Examples;

use PhpParser\BuilderFactory;
use PhpParser\ConstExprEvaluator;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Expr\Yield_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * Writing: 30 minutes
 */
final class SimplifyTestsRector extends AbstractRector
{
    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    public function __construct(
        ConstExprEvaluator $constExprEvaluator,
        DocBlockAnalyzer $docBlockAnalyzer,
        BuilderFactory $builderFactory
    ) {
        $this->constExprEvaluator = $constExprEvaluator;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->builderFactory = $builderFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Simplify tests - see PR #@todo', [new CodeSample('', '')]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, AbstractRectorTestCase::class)) {
            return null;
        }

        if ($node->isAbstract()) {
            return null;
        }

        $classMethodsByName = $this->getClassMethodByName($node);
        if (isset($classMethodsByName['test']) && empty($classMethodsByName['test']->params)) {
            // test()... method without any params, no provider → skip
            return null;
        }

        $rectorClass = null;

        if (! isset($classMethodsByName['provideConfig'])) {
            return null;
        }

        $stmts = $classMethodsByName['provideConfig']->stmts;
        $configPath = null;
        if ($stmts[0] instanceof Return_) {
            /** @var SplFileInfo $fileInfo */
            $fileInfo = $node->getAttribute(Attribute::FILE_INFO);
            $configPath = $fileInfo->getPath() . $this->constExprEvaluator->evaluateDirectly($stmts[0]->expr);

            $rectorClass = $this->matchSingleServiceWithoutConfigInFile($configPath);
        }

        // not just single rector config
        if ($configPath === null || ! is_string($rectorClass)) {
            return null;
        }

        // add "getRectorClass" method

        $returnNode = new Return_(new ClassConstFetch(new FullyQualified($rectorClass), 'class'));

        $classMethod = $this->builderFactory->method('getRectorClass')
            ->makePublic()
            ->setReturnType('string')
            ->addStmt($returnNode)
            ->getNode();

        $node->stmts[] = $classMethod;

        // remove "provideConfig" method
        $this->removeNode($classMethodsByName['provideConfig']);

        // remove @covers annotation
        $this->docBlockAnalyzer->removeTagFromNode($node, 'covers');

        // merge "test" + "provideFiles"
        if (isset($classMethodsByName['test']) && isset($classMethodsByName['provideFiles'])) {
            $this->removeNode($classMethodsByName['test']);

            $provideFilesClassMethod = $classMethodsByName['provideFiles'];
            $provideFilesClassMethod->returnType = new Identifier('void');
            $provideFilesClassMethod->name = new Name('test');

            // collect yields to array + wrap with method call

            $arrayItems = [];

            foreach ($provideFilesClassMethod->stmts as $stmt) {
                if ($stmt instanceof Expression) {
                    if ($stmt->expr instanceof Yield_) {
                        $arrayItems[] = $stmt->expr->value;
                        $this->removeNode($stmt);
                    }
                }
            }

            $doTestFilesMethodCall = new MethodCall(new Variable('this'), 'doTestFiles');
            $doTestFilesMethodCall->args[] = new Arg(new Array_($arrayItems));

            $provideFilesClassMethod->stmts = [new Expression($doTestFilesMethodCall)];
        }

        if ($configPath) {
            // remove config file, not needed anymore
            unlink($configPath);
        }

        return $node;
    }

    /**
     * @return ClassMethod[]
     */
    private function getClassMethodByName(Class_ $classNode): array
    {
        $classMethodsByName = [];
        foreach ($classNode->stmts as $stmt) {
            if (! $stmt instanceof ClassMethod) {
                continue;
            }

            $classMethodsByName[$this->getName($stmt)] = $stmt;
        }

        return $classMethodsByName;
    }

    private function matchSingleServiceWithoutConfigInFile(string $configPath): ?string
    {
        if (! file_exists($configPath)) {
            return null;
        }

        $yaml = Yaml::parseFile($configPath);

        if (count($yaml) !== 1) {
            return null;
        }

        if (! isset($yaml['services'])) {
            return null;
        }

        if (count($yaml['services']) !== 1) {
            return null;
        }

        $serviceName = key($yaml['services']);
        if ($yaml['services'][$serviceName] === null) {
            return $serviceName;
        }

        return null;
    }
}
