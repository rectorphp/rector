<?php

declare (strict_types=1);
namespace Rector\DowngradePhp83\Rector\ClassConst;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassConst;
use Rector\NodeManipulator\PropertyDecorator;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/typed_class_constants
 *
 * @see \Rector\Tests\DowngradePhp83\Rector\ClassConst\DowngradeTypedClassConstRector\DowngradeTypedClassConstRectorTest
 */
final class DowngradeTypedClassConstRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeManipulator\PropertyDecorator
     */
    private $propertyDecorator;
    public function __construct(PropertyDecorator $propertyDecorator)
    {
        $this->propertyDecorator = $propertyDecorator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassConst::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove typed class constant', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeClass
{
    public string FOO = 'test';
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    /**
     * @var string
     */
    public FOO = 'test';
}
CODE_SAMPLE
)]);
    }
    /**
     * @param ClassConst $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->type instanceof Node) {
            return null;
        }
        $this->propertyDecorator->decorateWithDocBlock($node, $node->type);
        $node->type = null;
        return $node;
    }
}
