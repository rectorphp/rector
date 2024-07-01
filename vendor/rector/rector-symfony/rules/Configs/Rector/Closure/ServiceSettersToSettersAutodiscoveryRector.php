<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\MinimalSharedStringSolver;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Rector\Symfony\ValueObject\ClassNameAndFilePath;
use RectorPrefix202407\Symfony\Component\Filesystem\Filesystem;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Configs\Rector\Closure\ServiceSettersToSettersAutodiscoveryRector\ServiceSettersToSettersAutodiscoveryRectorTest
 */
final class ServiceSettersToSettersAutodiscoveryRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector
     */
    private $symfonyPhpClosureDetector;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    /**
     * @readonly
     * @var \Symfony\Component\Filesystem\Filesystem
     */
    private $filesystem;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @readonly
     * @var \Rector\Symfony\MinimalSharedStringSolver
     */
    private $minimalSharedStringSolver;
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, ReflectionProvider $reflectionProvider, Filesystem $filesystem, ValueResolver $valueResolver)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->reflectionProvider = $reflectionProvider;
        $this->filesystem = $filesystem;
        $this->valueResolver = $valueResolver;
        $this->minimalSharedStringSolver = new MinimalSharedStringSolver();
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $services->set(..., ...) to $services->load(..., ...) where meaningful', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use App\Services\FistService;
use App\Services\SecondService;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $services = $containerConfigurator->services();

    $services->set(FistService::class);
    $services->set(SecondService::class);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $services = $containerConfigurator->services();

    $services->load('App\\Services\\', '../src/Services/*');
};
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Closure::class];
    }
    /**
     * @param Closure $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        /** @var array<Expression<MethodCall>> $bareServicesSetMethodCallExpressions */
        $bareServicesSetMethodCallExpressions = $this->collectServiceSetMethodCallExpressions($node);
        if ($bareServicesSetMethodCallExpressions === []) {
            return null;
        }
        $classNamesAndFilesPaths = $this->createClassNamesAndFilePaths($bareServicesSetMethodCallExpressions);
        $classNames = \array_map(static function (ClassNameAndFilePath $classNameAndFilePath) : string {
            return $classNameAndFilePath->getClassName();
        }, $classNamesAndFilesPaths);
        $sharedNamespace = $this->minimalSharedStringSolver->solve(...$classNames);
        $firstClassNameAndFilePath = $classNamesAndFilesPaths[0];
        $classFilePath = $firstClassNameAndFilePath->getFilePath();
        $directoryConcat = $this->createAbsolutePathConcat($classFilePath);
        $loadMethodCall = $this->createServicesLoadMethodCall($sharedNamespace, $directoryConcat);
        $node->stmts[] = new Expression($loadMethodCall);
        $this->removeServicesSetMethodCalls($node, $bareServicesSetMethodCallExpressions);
        return $node;
    }
    public function isBareServicesSetMethodCall(MethodCall $methodCall) : bool
    {
        if (!$this->isName($methodCall->name, 'set')) {
            return \false;
        }
        if (!$this->isObjectType($methodCall->var, new ObjectType('Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ServicesConfigurator'))) {
            return \false;
        }
        // must have exactly single argument
        if (\count($methodCall->getArgs()) !== 1) {
            return \false;
        }
        $firstArg = $methodCall->getArgs()[0];
        // first argument must be a class name, e.g. SomeClass::class
        return $firstArg->value instanceof ClassConstFetch;
    }
    /**
     * @return array<Expression<MethodCall>>
     */
    private function collectServiceSetMethodCallExpressions(Closure $closure) : array
    {
        $servicesSetMethodCalls = [];
        foreach ($closure->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            if (!$stmt->expr instanceof MethodCall) {
                continue;
            }
            $methodCall = $stmt->expr;
            if (!$this->isBareServicesSetMethodCall($methodCall)) {
                continue;
            }
            $servicesSetMethodCalls[] = $stmt;
        }
        return $servicesSetMethodCalls;
    }
    /**
     * @param array<Expression<MethodCall>> $methodsCallExpressions
     * @return ClassNameAndFilePath[]
     */
    private function createClassNamesAndFilePaths(array $methodsCallExpressions) : array
    {
        $classNamesAndFilesPaths = [];
        foreach ($methodsCallExpressions as $methodCallExpression) {
            /** @var MethodCall $methodCall */
            $methodCall = $methodCallExpression->expr;
            $firstArg = $methodCall->getArgs()[0];
            $serviceClassReference = $this->valueResolver->getValue($firstArg->value);
            if (!\is_string($serviceClassReference)) {
                throw new ShouldNotHappenException();
            }
            $classReflection = $this->reflectionProvider->getClass($serviceClassReference);
            // we only work with known local classes
            if ($classReflection->isInternal()) {
                continue;
            }
            $filename = $classReflection->getFileName();
            if (!\is_string($filename)) {
                continue;
            }
            $classNamesAndFilesPaths[] = new ClassNameAndFilePath($classReflection->getName(), $filename);
        }
        return $classNamesAndFilesPaths;
    }
    private function createAbsolutePathConcat(string $classFilePath) : Concat
    {
        $relativeDirectoryPath = $this->filesystem->makePathRelative(\dirname($classFilePath), \dirname($this->file->getFilePath()));
        $distConstFetch = new ConstFetch(new Name('__DIR__'));
        return new Concat($distConstFetch, new String_('/' . $relativeDirectoryPath));
    }
    private function createServicesLoadMethodCall(string $sharedNamespace, Concat $directoryConcat) : MethodCall
    {
        $args = [new Arg(new String_($sharedNamespace)), new Arg($directoryConcat)];
        return new MethodCall(new Variable('services'), 'load', $args);
    }
    /**
     * @param Stmt[] $stmtsToRemove
     */
    private function removeServicesSetMethodCalls(Closure $closure, array $stmtsToRemove) : void
    {
        foreach ($closure->stmts as $key => $stmt) {
            foreach ($stmtsToRemove as $stmtToRemove) {
                if ($stmt === $stmtToRemove) {
                    unset($closure->stmts[$key]);
                    continue 2;
                }
            }
        }
    }
}
