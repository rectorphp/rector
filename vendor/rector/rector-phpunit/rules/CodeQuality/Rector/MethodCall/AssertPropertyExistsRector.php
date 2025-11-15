<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as this assert method was removed in PHPUnit 10 @see https://github.com/sebastianbergmann/phpunit/issues/4601
 */
final class AssertPropertyExistsRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns `property_exists` comparisons to their method name alternatives in PHPUnit TestCase', [new CodeSample(<<<'CODE_SAMPLE'
$this->assertFalse(property_exists(new Class, "property"));
$this->assertTrue(property_exists(new Class, "property"));
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->assertClassHasAttribute("property", "Class");
$this->assertClassNotHasAttribute("property", "Class");
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" rule is deprecated as the attribute exists assert method was removed in PHPUnit 10.', self::class));
    }
}
