<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\MagicDisclosure\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeFactory\NonFluentChainMethodCallFactory;
use Rector\MagicDisclosure\NodeManipulator\FluentChainMethodCallRootExtractor;
use Rector\MagicDisclosure\Rector\AbstractRector\AbstractConfigurableMatchTypeRector;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;

abstract class AbstractFluentChainMethodCallRector extends AbstractConfigurableMatchTypeRector implements ConfigurableRectorInterface
{
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
     * @required
     */
    public function autowireAbstractFluentChainMethodCallRector(
        FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer,
        FluentChainMethodCallRootExtractor $fluentChainMethodCallRootExtractor,
        NonFluentChainMethodCallFactory $nonFluentChainMethodCallFactory
    ): void {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->fluentChainMethodCallRootExtractor = $fluentChainMethodCallRootExtractor;
        $this->nonFluentChainMethodCallFactory = $nonFluentChainMethodCallFactory;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     */
    protected function shouldSkipChainMethodCalls(AssignAndRootExpr $assignAndRootExpr, array $chainMethodCalls): bool
    {
        $calleeUniqueTypes = $this->fluentChainMethodCallNodeAnalyzer->resolveCalleeUniqueTypes(
            $assignAndRootExpr,
            $chainMethodCalls
        );

        if (count($calleeUniqueTypes) !== 1) {
            return true;
        }

        $calleeUniqueType = $calleeUniqueTypes[0];

        if ($this->isKnownAllowedFluentType($calleeUniqueType)) {
            return true;
        }

        return ! $this->isMatchedType($calleeUniqueType);
    }

    /**
     * @return Node[][]|AssignAndRootExpr[]
     */
    protected function createStandaloneNodesToAddFromChainMethodCalls(MethodCall $methodCall, string $kind): array
    {
        $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($methodCall);
        $assignAndRootExpr = $this->fluentChainMethodCallRootExtractor->extractFromMethodCalls(
            $chainMethodCalls,
            $kind
        );

        if ($assignAndRootExpr === null) {
            return [];
        }

        if ($this->shouldSkipChainMethodCalls($assignAndRootExpr, $chainMethodCalls)) {
            return [];
        }

        $nodesToAdd = $this->nonFluentChainMethodCallFactory->createFromAssignObjectAndMethodCalls(
            $assignAndRootExpr,
            $chainMethodCalls,
            $kind
        );

        return [$nodesToAdd, $assignAndRootExpr];
    }

    private function isKnownAllowedFluentType(string $class): bool
    {
        // skip query and builder
        // @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/ "When does a fluent interface make sense?"
        if ((bool) Strings::match($class, '#(Finder|Query|Builder|MutatingScope)$#')) {
            return true;
        }

        // allowed fluent types
        return is_a($class, 'Nette\Forms\Controls\BaseControl', true);
    }
}
