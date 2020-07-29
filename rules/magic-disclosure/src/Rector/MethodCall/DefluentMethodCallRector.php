<?php

declare(strict_types=1);

namespace Rector\MagicDisclosure\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\AssignAndRootExpr;
use Rector\MagicDisclosure\Matcher\ClassNameTypeMatcher;
use Rector\MagicDisclosure\NodeAnalyzer\ChainMethodCallNodeAnalyzer;
use Rector\MagicDisclosure\NodeFactory\NonFluentMethodCallFactory;
use Rector\MagicDisclosure\NodeManipulator\ChainMethodCallRootExtractor;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 * @see https://www.yegor256.com/2018/03/13/fluent-interfaces.html
 *
 * @see \Rector\MagicDisclosure\Tests\Rector\MethodCall\DefluentMethodCallRector\DefluentMethodCallRectorTest
 */
final class DefluentMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const NAMES_TO_DEFLUENT = '$namesToDefluent';

    /**
     * @var string[]
     */
    private $namesToDefluent = [];

    /**
     * @var ChainMethodCallNodeAnalyzer
     */
    private $chainMethodCallNodeAnalyzer;

    /**
     * @var ChainMethodCallRootExtractor
     */
    private $chainMethodCallRootExtractor;

    /**
     * @var ClassNameTypeMatcher
     */
    private $classNameTypeMatcher;

    /**
     * @var NonFluentMethodCallFactory
     */
    private $nonFluentMethodCallFactory;

    public function __construct(
        ChainMethodCallNodeAnalyzer $chainMethodCallNodeAnalyzer,
        ChainMethodCallRootExtractor $chainMethodCallRootExtractor,
        ClassNameTypeMatcher $classNameTypeMatcher,
        NonFluentMethodCallFactory $nonFluentMethodCallFactory
    ) {
        $this->chainMethodCallNodeAnalyzer = $chainMethodCallNodeAnalyzer;
        $this->chainMethodCallRootExtractor = $chainMethodCallRootExtractor;
        $this->classNameTypeMatcher = $classNameTypeMatcher;
        $this->nonFluentMethodCallFactory = $nonFluentMethodCallFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns fluent interface calls to classic ones.', [new CodeSample(<<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction()
            ->otherFunction();
PHP
            , <<<'PHP'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
PHP
        )]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, Return_::class];
    }

    /**
     * @param MethodCall|Return_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $methodCall = $this->matchMethodCall($node);
        if ($methodCall === null) {
            return null;
        }

        if ($this->isHandledByReturn($node)) {
            return null;
        }

        if (! $this->chainMethodCallNodeAnalyzer->isLastChainMethodCall($methodCall)) {
            return null;
        }

        $chainMethodCalls = $this->chainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($methodCall);
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

        $this->removeCurrentNode($node);

        foreach ($nodesToAdd as $nodeToAdd) {
            // needed to remove weird spacing
            $nodeToAdd->setAttribute('origNode', null);
            $this->addNodeAfterNode($nodeToAdd, $node);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->namesToDefluent = $configuration[self::NAMES_TO_DEFLUENT] ?? [];
    }

    /**
     * @param MethodCall|Return_ $node
     */
    private function matchMethodCall(Node $node): ?MethodCall
    {
        if ($node instanceof Return_) {
            if ($node->expr === null) {
                return null;
            }

            if ($node->expr instanceof MethodCall) {
                return $node->expr;
            }
            return null;
        }

        return $node;
    }

    /**
     * @param MethodCall|Return_ $node
     */
    private function isHandledByReturn(Node $node): bool
    {
        if ($node instanceof MethodCall) {
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            // handled ty Return_ node
            if ($parentNode instanceof Return_) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param MethodCall[] $chainMethodCalls
     */
    private function shouldSkip(AssignAndRootExpr $assignAndRootExpr, array $chainMethodCalls): bool
    {
        if (! $this->chainMethodCallNodeAnalyzer->isCalleeSingleType($assignAndRootExpr, $chainMethodCalls)) {
            return true;
        }

        return ! $this->classNameTypeMatcher->doesExprMatchNames(
            $assignAndRootExpr->getRootExpr(),
            $this->namesToDefluent
        );
    }

    /**
     * @param MethodCall|Return_ $node
     */
    private function removeCurrentNode(Node $node): void
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($parentNode instanceof Assign) {
            $this->removeNode($parentNode);
            return;
        }

        $this->removeNode($node);
    }
}
