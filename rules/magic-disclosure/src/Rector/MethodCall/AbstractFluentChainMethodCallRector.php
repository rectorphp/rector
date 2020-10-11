<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\MagicDisclosure\NodeAnalyzer\ChainCallsStaticTypeResolver;
use Rector\MagicDisclosure\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeFactory\NonFluentChainMethodCallFactory;
use Rector\MagicDisclosure\NodeManipulator\FluentChainMethodCallRootExtractor;
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
    private const ALLOWED_TYPES = [
        'Symfony\Component\DependencyInjection\Loader\Configurator\AbstractConfigurator',
        'Nette\Forms\Controls\BaseControl',
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
     * @required
     */
    public function autowireAbstractFluentChainMethodCallRector(
        FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer,
        FluentChainMethodCallRootExtractor $fluentChainMethodCallRootExtractor,
        NonFluentChainMethodCallFactory $nonFluentChainMethodCallFactory,
        ChainCallsStaticTypeResolver $chainCallsStaticTypeResolver
    ): void {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->fluentChainMethodCallRootExtractor = $fluentChainMethodCallRootExtractor;
        $this->nonFluentChainMethodCallFactory = $nonFluentChainMethodCallFactory;
        $this->chainCallsStaticTypeResolver = $chainCallsStaticTypeResolver;
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

        if (! $this->isCorrectTypeCount($calleeUniqueTypes, $assignAndRootExpr)) {
            return true;
        }

        $calleeUniqueType = $this->resolveCalleeUniqueType($assignAndRootExpr, $calleeUniqueTypes);

        return $this->isAllowedType($calleeUniqueType, self::ALLOWED_TYPES);
    }

    protected function createStandaloneNodesToAddFromChainMethodCalls(
        MethodCall $methodCall,
        string $kind
    ): ?AssignAndRootExprAndNodesToAdd {
        $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($methodCall);

        if (! $this->areAllClassMethodLocatedInSameClass($chainMethodCalls)) {
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
     * @param MethodCall[] $chainMethodCalls
     */
    protected function areAllClassMethodLocatedInSameClass(array $chainMethodCalls): bool
    {
        // are method calls located in the same class?
        $classOfClassMethod = [];
        foreach ($chainMethodCalls as $chainMethodCall) {
            $classMethod = $this->nodeRepository->findClassMethodByMethodCall($chainMethodCall);

            if ($classMethod instanceof ClassMethod) {
                $classOfClassMethod[] = $classMethod->getAttribute(AttributeKey::CLASS_NAME);
            } else {
                $classOfClassMethod[] = null;
            }
        }

        return count(array_unique($classOfClassMethod)) <= 1;
    }

    /**
     * @param string[] $calleeUniqueTypes
     */
    private function isCorrectTypeCount(array $calleeUniqueTypes, AssignAndRootExpr $assignAndRootExpr): bool
    {
        if (count($calleeUniqueTypes) === 0) {
            return false;
        }

        if ($assignAndRootExpr->isFirstCallFactory()) {
            return count($calleeUniqueTypes) === 2;
        }

        return count($calleeUniqueTypes) === 1;
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
