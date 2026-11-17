<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\Stmt;

use PhpParser\Node;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\PhpParser\Enum\NodeGroup;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
/**
 * @deprecated This rule is deprecated, as newlines between statements are coding standard concern and its definition is too vague. Use coding standard tool like ECS or PHP CS Fixer instead.
 */
final class NewlineAfterStatementRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add empty new line after different-type statements to improve code readability', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run($input)
    {
        $value = 5 * $input;
        $secondValue = $value ^ 5;
        return $value - $secondValue;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run($input)
    {
        $value = 5 * $input;

        $secondValue = $value ^ 5;

        return $value - $secondValue;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return NodeGroup::STMTS_AWARE;
    }
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated, as newlines between statements are coding standard concern; use coding standard tool like ECS or PHP CS Fixer instead', self::class));
    }
}
