<?php

declare(strict_types=1);

namespace Rector\Defluent\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Return_;
use Rector\Defluent\Rector\AbstractFluentChainMethodCallRector;
use Rector\Defluent\ValueObject\AssignAndRootExprAndNodesToAdd;
use Rector\Defluent\ValueObject\FluentCallsKind;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\PackageBuilder\Php\TypeChecker;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://ocramius.github.io/blog/fluent-interfaces-are-evil/
 * @see https://www.yegor256.com/2018/03/13/fluent-interfaces.html
 *
 * @see \Rector\Defluent\Tests\Rector\MethodCall\FluentChainMethodCallToNormalMethodCallRector\FluentChainMethodCallToNormalMethodCallRectorTest
 * @see \Rector\Defluent\Tests\Rector\MethodCall\NewFluentChainMethodCallToNonFluentRector\NewFluentChainMethodCallToNonFluentRectorTest
 */
final class NewFluentChainMethodCallToNonFluentRector extends AbstractFluentChainMethodCallRector
{
    /**
     * @var TypeChecker
     */
    private $typeChecker;

    public function __construct(TypeChecker $typeChecker)
    {
        $this->typeChecker = $typeChecker;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Turns fluent interface calls to classic ones.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
(new SomeClass())->someFunction()
            ->otherFunction();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someClass = new SomeClass();
$someClass->someFunction();
$someClass->otherFunction();
CODE_SAMPLE
                ),

            ]);
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
        // handled by another rule
        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);
        if ($this->typeChecker->isInstanceOf($parent, [Return_::class, Arg::class])) {
            return null;
        }

        if ($this->shouldSkipMethodCall($node)) {
            return null;
        }

        $assignAndRootExprAndNodesToAdd = $this->createStandaloneNodesToAddFromChainMethodCalls(
            $node,
            FluentCallsKind::NORMAL
        );
        if (! $assignAndRootExprAndNodesToAdd instanceof AssignAndRootExprAndNodesToAdd) {
            return null;
        }

        $this->removeCurrentNode($node);
        $this->addNodesAfterNode($assignAndRootExprAndNodesToAdd->getNodesToAdd(), $node);

        return null;
    }
}
