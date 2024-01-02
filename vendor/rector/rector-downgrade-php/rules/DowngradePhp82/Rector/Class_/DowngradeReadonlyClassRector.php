<?php

declare (strict_types=1);
namespace Rector\DowngradePhp82\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/readonly_classes
 *
 * @see \Rector\Tests\DowngradePhp82\Rector\Class_\DowngradeReadonlyClassRector\DowngradeReadonlyClassRectorTest
 */
final class DowngradeReadonlyClassRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(VisibilityManipulator $visibilityManipulator)
    {
        $this->visibilityManipulator = $visibilityManipulator;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove "readonly" class type, decorate all properties to "readonly"', [new CodeSample(<<<'CODE_SAMPLE'
final readonly class SomeClass
{
    public string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public readonly string $foo;

    public function __construct()
    {
        $this->foo = 'foo';
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->visibilityManipulator->isReadonly($node)) {
            return null;
        }
        $this->visibilityManipulator->removeReadonly($node);
        $this->makePropertiesReadonly($node);
        $this->makePromotedPropertiesReadonly($node);
        return $node;
    }
    private function makePropertiesReadonly(Class_ $class) : void
    {
        foreach ($class->getProperties() as $property) {
            if ($property->isReadonly()) {
                continue;
            }
            /**
             * It technically impossible that readonly class has:
             *
             *  - non-typed property
             *  - static property
             *
             * but here to ensure no flip-flop when using direct rule for multiple rules applied
             */
            if ($property->type === null) {
                continue;
            }
            if ($property->isStatic()) {
                continue;
            }
            $this->visibilityManipulator->makeReadonly($property);
        }
    }
    private function makePromotedPropertiesReadonly(Class_ $class) : void
    {
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$classMethod instanceof ClassMethod) {
            return;
        }
        foreach ($classMethod->getParams() as $param) {
            if ($this->visibilityManipulator->isReadonly($param)) {
                continue;
            }
            /**
             * not property promotion, just param
             */
            if ($param->flags === 0) {
                continue;
            }
            /**
             * also not typed, just param
             */
            if ($param->type === null) {
                continue;
            }
            $this->visibilityManipulator->makeReadonly($param);
        }
    }
}
