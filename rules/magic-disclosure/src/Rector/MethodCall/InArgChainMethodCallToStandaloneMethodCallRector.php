<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\MagicDisclosure\NodeAnalyzer\ChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeAnalyzer\NewChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeFactory\NonFluentMethodCallFactory;
use Rector\MagicDisclosure\NodeManipulator\ChainMethodCallRootExtractor;
use Rector\MagicDisclosure\Rector\AbstractRector\AbstractConfigurableMatchTypeRector;
use Rector\MagicDisclosure\ValueObject\AssignAndRootExpr;
use Rector\NetteKdyby\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 *
 * @see \Rector\MagicDisclosure\Tests\Rector\MethodCall\InArgChainMethodCallToStandaloneMethodCallRector\InArgChainMethodCallToStandaloneMethodCallRectorTest
 */
final class InArgChainMethodCallToStandaloneMethodCallRector extends AbstractConfigurableMatchTypeRector implements ConfigurableRectorInterface
{
    /**
     * @var ChainMethodCallNodeAnalyzer
     */
    private $chainMethodCallNodeAnalyzer;

    /**
     * @var NonFluentMethodCallFactory
     */
    private $nonFluentMethodCallFactory;

    /**
     * @var VariableNaming
     */
    private $variableNaming;

    /**
     * @var NewChainMethodCallNodeAnalyzer
     */
    private $newChainMethodCallNodeAnalyzer;

    /**
     * @var ChainMethodCallRootExtractor
     */
    private $chainMethodCallRootExtractor;

    public function __construct(
        ChainMethodCallNodeAnalyzer $chainMethodCallNodeAnalyzer,
        NonFluentMethodCallFactory $nonFluentMethodCallFactory,
        VariableNaming $variableNaming,
        NewChainMethodCallNodeAnalyzer $newChainMethodCallNodeAnalyzer,
        ChainMethodCallRootExtractor $chainMethodCallRootExtractor
    ) {
        $this->chainMethodCallNodeAnalyzer = $chainMethodCallNodeAnalyzer;
        $this->nonFluentMethodCallFactory = $nonFluentMethodCallFactory;
        $this->variableNaming = $variableNaming;
        $this->newChainMethodCallNodeAnalyzer = $newChainMethodCallNodeAnalyzer;
        $this->chainMethodCallRootExtractor = $chainMethodCallRootExtractor;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [new CodeSample(<<<'PHP'
class UsedAsParameter
{
    public function someFunction(FluentClass $someClass)
    {
        $this->processFluentClass($someClass->someFunction()->otherFunction());
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}

PHP
            , <<<'PHP'
class UsedAsParameter
{
    public function someFunction(FluentClass $someClass)
    {
        $someClass->someFunction();
        $someClass->otherFunction();
        $this->processFluentClass($someClass);
    }

    public function processFluentClass(FluentClass $someClass)
    {
    }
}
PHP
        )]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->hasParentType($node, Arg::class)) {
            return null;
        }

        /** @var Arg $arg */
        $arg = $node->getAttribute(AttributeKey::PARENT_NODE);
        /** @var Node|null $parentMethodCall */
        $parentMethodCall = $arg->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parentMethodCall instanceof MethodCall) {
            return null;
        }

        if (! $this->chainMethodCallNodeAnalyzer->isLastChainMethodCall($node)) {
            return null;
        }

        // create instances from (new ...)->call, re-use from
        if ($node->var instanceof New_) {
            $this->refactorNew($node, $node->var);
            return null;
        }

        // DUPLCIATED
        $chainMethodCalls = $this->chainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($node);

        $assignAndRootExpr = $this->chainMethodCallRootExtractor->extractFromMethodCalls($chainMethodCalls);
        if ($assignAndRootExpr === null) {
            return null;
        }

        if ($this->shouldSkip($assignAndRootExpr, $chainMethodCalls)) {
            return null;
        }

        $nodesToAdd = $this->nonFluentMethodCallFactory->createFromAssignObjectAndMethodCalls(
            $assignAndRootExpr,
            $chainMethodCalls
        );

        $this->addNodesBeforeNode($nodesToAdd, $node);

        return $assignAndRootExpr->getCallerExpr();
    }

    private function removeParentParent(MethodCall $node): void
    {
        /** @var Arg $parent */
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);

        /** @var MethodCall $parentParent */
        $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);

        $this->removeNode($parentParent);
    }

    private function createFluentAsArg(MethodCall $methodCall, Variable $variable): MethodCall
    {
        /** @var Arg $parent */
        $parent = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        /** @var MethodCall $parentParent */
        $parentParent = $parent->getAttribute(AttributeKey::PARENT_NODE);

        $lastMethodCall = new MethodCall($parentParent->var, $parentParent->name);
        $lastMethodCall->args[] = new Arg($variable);

        return $lastMethodCall;
    }

    private function crateVariableFromNew(New_ $new): Variable
    {
        $variableName = $this->variableNaming->resolveFromNode($new);
        return new Variable($variableName);
    }

    private function refactorNew(MethodCall $methodCall, New_ $new): void
    {
        if (! $this->newChainMethodCallNodeAnalyzer->isNewMethodCallReturningSelf($methodCall)) {
            return;
        }

        $nodesToAdd = $this->nonFluentMethodCallFactory->createFromNewAndRootMethodCall($new, $methodCall);

        $newVariable = $this->crateVariableFromNew($new);
        $nodesToAdd[] = $this->createFluentAsArg($methodCall, $newVariable);

        $this->addNodesBeforeNode($nodesToAdd, $methodCall);
        $this->removeParentParent($methodCall);
    }

    /**
     * @duplicated
     * @param MethodCall[] $chainMethodCalls
     */
    private function shouldSkip(AssignAndRootExpr $assignAndRootExpr, array $chainMethodCalls): bool
    {
        $calleeUniqueTypes = $this->chainMethodCallNodeAnalyzer->resolveCalleeUniqueTypes(
            $assignAndRootExpr,
            $chainMethodCalls
        );

        if (count($calleeUniqueTypes) !== 1) {
            return true;
        }

        $calleeUniqueType = $calleeUniqueTypes[0];
        // skip query and builder
        // @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/ "When does a fluent interface make sense?"
        if ((bool) Strings::match($calleeUniqueType, '#(Query|Builder)$#')) {
            return true;
        }

        return ! $this->isMatchedType($calleeUniqueType);
    }
}
