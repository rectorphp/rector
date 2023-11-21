<?php

declare (strict_types=1);
namespace Rector\TypeDeclaration\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @api
 * @deprecated This rule is deprecated as created invalid code. Use other rules from TYPE_DECLARATION instead
 */
final class TypedPropertyFromStrictGetterMethodReturnTypeRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Complete property type based on getter strict types', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public $name;

    public function getName(): string|null
    {
        return $this->name;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public ?string $name = null;

    public function getName(): string|null
    {
        return $this->name;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Stmt\Class_
    {
        \trigger_error('This rule is deprecated as created invalid code. Use other rules from TYPE_DECLARATION instead');
        \sleep(3);
        return null;
    }
}
