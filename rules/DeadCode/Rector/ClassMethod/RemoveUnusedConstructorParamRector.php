<?php

declare(strict_types=1);

namespace Rector\DeadCode\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\NodeAnalyzer\ParamAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DeadCode\Rector\ClassMethod\RemoveUnusedConstructorParamRector\RemoveUnusedConstructorParamRectorTest
 */
final class RemoveUnusedConstructorParamRector extends AbstractRector
{
    public function __construct(
        private ParamAnalyzer $paramAnalyzer
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Remove unused parameter in constructor', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private $hey;

    public function __construct($hey, $man)
    {
        $this->hey = $hey;
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    private $hey;

    public function __construct($hey)
    {
        $this->hey = $hey;
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }

        if ($node->params === []) {
            return null;
        }

        if ($this->paramAnalyzer->hasPropertyPromotion($node->params)) {
            return null;
        }

        $class = $this->betterNodeFinder->findParentType($node, Class_::class);
        if (! $class instanceof Class_) {
            return null;
        }

        if ($node->isAbstract()) {
            return null;
        }

        foreach ($node->params as $param) {
            if ($this->paramAnalyzer->isParamUsedInClassMethod($node, $param)) {
                continue;
            }

            $this->nodeRemover->removeParam($node, $param);
        }

        return null;
    }
}
