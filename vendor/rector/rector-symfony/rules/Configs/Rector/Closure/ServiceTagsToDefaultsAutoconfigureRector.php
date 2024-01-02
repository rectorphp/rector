<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\SymfonyPhpClosureDetector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Configs\Rector\Closure\ServiceTagsToDefaultsAutoconfigureRector\ServiceTagsToDefaultsAutoconfigureRectorTest
 */
final class ServiceTagsToDefaultsAutoconfigureRector extends AbstractRector
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
     * @var string[]
     */
    private const AUTOCONFIGUREABLE_TAGS = [
        // @todo fill
        'twig.extension',
        'console.command',
        'kernel.event_subscriber',
        'monolog.logger',
        'security.voter',
    ];
    public function __construct(SymfonyPhpClosureDetector $symfonyPhpClosureDetector, ValueResolver $valueResolver)
    {
        $this->symfonyPhpClosureDetector = $symfonyPhpClosureDetector;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $services->set(..., ...)->tag(...) to $services->defaults()->autodiscovery() where meaningful', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use App\Command\SomeCommand;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(SomeCommand::class)
        ->tag('console.command');
};
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use App\Command\SomeCommand;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->defaults()
        ->autoconfigure();

    $services->set(SomeCommand::class);
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
        $hasDefaultsAutoconfigure = $this->symfonyPhpClosureDetector->hasDefaultsAutoconfigure($node);
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use(&$hasChanged) : ?Expr {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'tag')) {
                return null;
            }
            // is autoconfigureable tag?
            $firstArg = $node->getArgs()[0];
            $tagValue = $this->valueResolver->getValue($firstArg->value);
            if (!\in_array($tagValue, self::AUTOCONFIGUREABLE_TAGS, \true)) {
                return null;
            }
            // remove tag() method by returning nested method call
            $hasChanged = \true;
            return $node->var;
        });
        if ($hasChanged === \false) {
            return null;
        }
        if ($hasDefaultsAutoconfigure === \false) {
            $this->addDefaultAutoconfigure($node);
        }
        return $node;
    }
    private function createDefaultsAutoconfigureExpression() : Expression
    {
        $defaultsMethodCall = new MethodCall(new Variable('services'), 'defaults');
        $autoconfigureMethodCall = new MethodCall($defaultsMethodCall, 'autoconfigure');
        return new Expression($autoconfigureMethodCall);
    }
    private function addDefaultAutoconfigure(Closure $closure) : void
    {
        foreach ($closure->stmts as $key => $nodeStmt) {
            if (!$nodeStmt instanceof Expression) {
                continue;
            }
            if (!$nodeStmt->expr instanceof Assign) {
                continue;
            }
            $assign = $nodeStmt->expr;
            if (!$assign->var instanceof Variable) {
                continue;
            }
            if (!$this->isName($assign->var, 'services')) {
                continue;
            }
            // add defaults here, right after assign :)
            $autoconfigureExpression = $this->createDefaultsAutoconfigureExpression();
            \array_splice($closure->stmts, $key + 1, 0, [$autoconfigureExpression]);
            break;
        }
    }
}
