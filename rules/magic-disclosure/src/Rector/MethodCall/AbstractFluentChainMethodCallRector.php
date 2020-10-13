<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\MagicDisclosure\NodeAnalyzer\ChainCallsStaticTypeResolver;
use Rector\MagicDisclosure\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeAnalyzer\FluentChainMethodCallRootExtractor;
use Rector\MagicDisclosure\NodeAnalyzer\SameClassMethodCallAnalyzer;
use Rector\MagicDisclosure\NodeFactory\NonFluentChainMethodCallFactory;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExprAndNodesToAdd;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractFluentChainMethodCallRector extends AbstractRector
{
    /**
     * Skip query and builder
     * @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/ "When does a fluent interface make sense?
     *
     * @var string[]
     */
    private const ALLOWED_FLUENT_TYPES = [
        'Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator',
        'Nette\Forms\Controls\BaseControl',
        'Nette\DI\ContainerBuilder',
        'Nette\DI\Definitions\Definition',
        'Nette\DI\Definitions\ServiceDefinition',
        'PHPStan\Analyser\Scope',
        'DateTime',
        'Nette\Utils\DateTime',
        'DateTimeInterface',
        '*Finder',
        '*Builder',
        '*Query',
    ];

    /**
     * @var FluentChainMethodCallNodeAnalyzer
     */
    protected $fluentChainMethodCallNodeAnalyzer;

    /**
     * @var FluentChainMethodCallRootExtractor
     */
    protected $fluentChainMethodCallRootExtractor;

    /**
     * @var NonFluentChainMethodCallFactory
     */
    protected $nonFluentChainMethodCallFactory;

    /**
     * @var ChainCallsStaticTypeResolver
     */
    private $chainCallsStaticTypeResolver;

    /**
     * @var SameClassMethodCallAnalyzer
     */
    private $sameClassMethodCallAnalyzer;

    /**
     * @required
     */
    public function autowireAbstractFluentChainMethodCallRector(
        FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer,
        FluentChainMethodCallRootExtractor $fluentChainMethodCallRootExtractor,
        NonFluentChainMethodCallFactory $nonFluentChainMethodCallFactory,
        ChainCallsStaticTypeResolver $chainCallsStaticTypeResolver,
        SameClassMethodCallAnalyzer $sameClassMethodCallAnalyzer
    ): void {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->fluentChainMethodCallRootExtractor = $fluentChainMethodCallRootExtractor;
        $this->nonFluentChainMethodCallFactory = $nonFluentChainMethodCallFactory;
        $this->chainCallsStaticTypeResolver = $chainCallsStaticTypeResolver;
        $this->sameClassMethodCallAnalyzer = $sameClassMethodCallAnalyzer;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     */
    protected function shouldSkipChainMethodCalls(AssignAndRootExpr $assignAndRootExpr, array $chainMethodCalls): bool
    {
        $calleeUniqueTypes = $this->chainCallsStaticTypeResolver->resolveCalleeUniqueTypes(
            $assignAndRootExpr,
            $chainMethodCalls
        );

        if (! $this->sameClassMethodCallAnalyzer->isCorrectTypeCount($calleeUniqueTypes, $assignAndRootExpr)) {
            return true;
        }

        $calleeUniqueType = $this->resolveCalleeUniqueType($assignAndRootExpr, $calleeUniqueTypes);

        return $this->isAllowedType($calleeUniqueType, self::ALLOWED_FLUENT_TYPES);
    }

    protected function createStandaloneNodesToAddFromChainMethodCalls(
        MethodCall $methodCall,
        string $kind
    ): ?AssignAndRootExprAndNodesToAdd {
        $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($methodCall);
        if (! $this->sameClassMethodCallAnalyzer->haveSingleClass($chainMethodCalls)) {
            return null;
        }

        $assignAndRootExpr = $this->fluentChainMethodCallRootExtractor->extractFromMethodCalls(
            $chainMethodCalls,
            $kind
        );

        if ($assignAndRootExpr === null) {
            return null;
        }

        if ($this->shouldSkipChainMethodCalls($assignAndRootExpr, $chainMethodCalls)) {
            return null;
        }

        $nodesToAdd = $this->nonFluentChainMethodCallFactory->createFromAssignObjectAndMethodCalls(
            $assignAndRootExpr,
            $chainMethodCalls,
            $kind
        );

        return new AssignAndRootExprAndNodesToAdd($assignAndRootExpr, $nodesToAdd);
    }

    /**
     * @duplicated
     * @param MethodCall|Return_ $node
     */
    protected function removeCurrentNode(Node $node): void
    {
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parent instanceof Assign) {
            $this->removeNode($parent);
            return;
        }

        // part of method call
        if ($parent instanceof Arg) {
            $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);
            if ($parentParent instanceof MethodCall) {
                $this->removeNode($parentParent);
            }
            return;
        }

        $this->removeNode($node);
    }

    /**
     * @param string[] $calleeUniqueTypes
     */
    private function resolveCalleeUniqueType(AssignAndRootExpr $assignAndRootExpr, array $calleeUniqueTypes): string
    {
        if (! $assignAndRootExpr->isFirstCallFactory()) {
            return $calleeUniqueTypes[0];
        }

        return $calleeUniqueTypes[1] ?? $calleeUniqueTypes[0];
    }
}
