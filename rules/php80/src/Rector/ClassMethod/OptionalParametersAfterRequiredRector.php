<?php

declare(strict_types=1);

namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://php.watch/versions/8.0#deprecate-required-param-after-optional
 *
 * @see \Rector\Php80\Tests\Rector\ClassMethod\OptionalParametersAfterRequiredRector\OptionalParametersAfterRequiredRectorTest
 */
final class OptionalParametersAfterRequiredRector extends AbstractRector
{
    /**
     * @var \Rector\Php80\NodeResolver\RequireOptionalParamResolver
     */
    private $requireOptionalParamResolver;

    public function __construct(\Rector\Php80\NodeResolver\RequireOptionalParamResolver $requireOptionalParamResolver)
    {
        $this->requireOptionalParamResolver = $requireOptionalParamResolver;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move required parameters after optional ones', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($optional = 1, $required)
    {
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
class SomeObject
{
    public function run($required, $optional = 1)
    {
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, New_::class];
    }

    /**
     * @param ClassMethod|New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof ClassMethod) {
            return $this->refactorClassMethod($node);
        }

        return $this->refactorNew($node);
    }

    private function refactorClassMethod(ClassMethod $node): ?ClassMethod
    {
        if ($node->params === []) {
            return null;
        }

        $expectedOrderParams = $this->requireOptionalParamResolver->resolve($node);
        if ($node->params === $expectedOrderParams) {
            return null;
        }

        $node->params = $expectedOrderParams;

        return $node;
    }

    private function refactorNew(New_ $new): ?New_
    {
        dump($new);
        die;
    }
}
