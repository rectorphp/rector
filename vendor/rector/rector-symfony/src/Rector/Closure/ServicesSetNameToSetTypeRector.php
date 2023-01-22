<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\Closure\ServicesSetNameToSetTypeRector\ServicesSetNameToSetTypeRectorTest
 */
final class ServicesSetNameToSetTypeRector extends AbstractRector
{
    /**
     * @var array<string, string>
     */
    private $alreadyChangedServiceNamesToTypes = [];
    /**
     * @var bool
     */
    private $hasChanged = \false;
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector
     */
    private $symfonyPhpClosureDetector;
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $services->set("name_type", SomeType::class) to bare type, useful since Symfony 3.4', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set('some_name', App\SomeClass::class);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(App\SomeClass::class);
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
        $this->hasChanged = \false;
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        $this->handleSetServices($node);
        $this->handleRefServiceFunctionReferences($node);
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    private function isSetServices(MethodCall $methodCall) : bool
    {
        if (!$this->isName($methodCall->name, 'set')) {
            return \false;
        }
        return $this->isObjectType($methodCall->var, new ObjectType('Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ServicesConfigurator'));
    }
    private function handleSetServices(Closure $closure) : void
    {
        $this->traverseNodesWithCallable($closure->stmts, function (Node $node) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isSetServices($node)) {
                return null;
            }
            // must be exactly 2 args
            if (\count($node->args) !== 2) {
                return null;
            }
            // exchange type and service name
            $args = $node->getArgs();
            $firstArg = $args[0];
            if (!$firstArg->value instanceof String_) {
                return null;
            }
            $secondArg = $args[1];
            /** @var string $serviceName */
            $serviceName = $this->valueResolver->getValue($firstArg->value);
            $serviceType = $this->valueResolver->getValue($secondArg->value);
            if (!\is_string($serviceType)) {
                return null;
            }
            $this->alreadyChangedServiceNamesToTypes[$serviceName] = $serviceType;
            // move 2nd arg to 1st position
            $node->args = [$args[1]];
            $this->hasChanged = \true;
            return $node;
        });
    }
    private function handleRefServiceFunctionReferences(Closure $closure) : void
    {
        $this->traverseNodesWithCallable($closure, function (Node $node) : ?Node {
            if (!$node instanceof FuncCall) {
                return null;
            }
            if (!$this->isNames($node->name, ['Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\service', 'Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ref'])) {
                return null;
            }
            $args = $node->getArgs();
            if ($args === []) {
                return null;
            }
            $firstArg = $args[0];
            foreach ($this->alreadyChangedServiceNamesToTypes as $serviceName => $serviceType) {
                if (!$this->valueResolver->isValue($firstArg->value, $serviceName)) {
                    continue;
                }
                // replace string value with type
                $classConstFetch = new ClassConstFetch(new FullyQualified($serviceType), 'class');
                $node->args = [new Arg($classConstFetch)];
                $this->hasChanged = \true;
            }
            return $node;
        });
    }
}
