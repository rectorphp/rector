<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\Closure;

use PhpParser\Node;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
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
        $hasChanged = \false;
        if (!$this->symfonyPhpClosureDetector->detect($node)) {
            return null;
        }
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use(&$hasChanged) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            // @todo not parameters! check type :)
            if (!$this->isName($node->name, 'set')) {
                return null;
            }
            if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\DependencyInjection\\Loader\\Configurator\\ServicesConfigurator'))) {
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
            // move 2nd arg to 1st position
            $node->args = [$args[1]];
            $hasChanged = \true;
            return $node;
        });
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
