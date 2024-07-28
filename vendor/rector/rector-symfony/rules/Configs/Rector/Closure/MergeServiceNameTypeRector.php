<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Configs\Rector\Closure\MergeServiceNameTypeRector\MergeServiceNameTypeRectorTest
 */
final class MergeServiceNameTypeRector extends AbstractRector implements DeprecatedInterface
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector
     */
    private $symfonyPhpClosureDetector;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var bool
     */
    private $hasChanged = \false;
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, ValueResolver $valueResolver)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Merge name === type service registration, $services->set(SomeType::class, SomeType::class)', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(\App\SomeClass::class, \App\SomeClass::class);
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(\App\SomeClass::class);
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
        if ($this->hasChanged) {
            return $node;
        }
        return null;
    }
    private function handleSetServices(Closure $closure) : void
    {
        $this->traverseNodesWithCallable($closure->stmts, function (Node $node) : ?MethodCall {
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
            $firstArg = $node->getArgs()[0];
            $secondArg = $node->getArgs()[1];
            /** @var string $serviceName */
            $serviceName = $this->valueResolver->getValue($firstArg->value);
            $serviceType = $this->valueResolver->getValue($secondArg->value);
            if (!\is_string($serviceType)) {
                return null;
            }
            if ($serviceName !== $serviceType) {
                return null;
            }
            // remove 2nd arg
            unset($node->args[1]);
            $this->hasChanged = \true;
            return $node;
        });
    }
    private function isSetServices(MethodCall $methodCall) : bool
    {
        if (!$this->isName($methodCall->name, 'set')) {
            return \false;
        }
        return $this->isObjectType($methodCall->var, new ObjectType('Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ServicesConfigurator'));
    }
}
