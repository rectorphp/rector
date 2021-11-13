<?php

declare(strict_types=1);

namespace Rector\DowngradePhp54\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\Rector\AbstractRector;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @changelog https://wiki.php.net/rfc/instance-method-call
 *
 * @see \Rector\Tests\DowngradePhp54\Rector\MethodCall\DowngradeInstanceMethodCallRector\DowngradeInstanceMethodCallRectorTest
 */
final class DowngradeInstanceMethodCallRector extends AbstractRector
{
    public function __construct(
        private VariableNaming $variableNaming
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Downgrade instance and method call/property access', [
            new CodeSample(
                <<<'CODE_SAMPLE'
echo (new \ReflectionClass('\\stdClass'))->getName();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$object = new \ReflectionClass('\\stdClass');
echo $object->getName();
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, PropertyFetch::class, ArrayDimFetch::class];
    }

    /**
     * @param ArrayDimFetch|MethodCall|PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $variable = $this->createVariable($node);
        $expression = new Expression(new Assign($variable, $node->var));

        $this->nodesToAddCollector->addNodeBeforeNode($expression, $node);
        $node->var = $variable;
        // necessary to remove useless parentheses
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);

        return $node;
    }

    private function shouldSkip(ArrayDimFetch|MethodCall|PropertyFetch $node): bool
    {
        if ($node->var instanceof New_) {
            return false;
        }

        return ! $node->var instanceof Clone_;
    }

    private function createVariable(Node $node): Variable
    {
        $currentStmt = $node->getAttribute(AttributeKey::CURRENT_STATEMENT);
        $scope = $currentStmt->getAttribute(AttributeKey::SCOPE);

        return new Variable($this->variableNaming->createCountedValueName('object', $scope));
    }
}
