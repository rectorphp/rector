<?php

declare (strict_types=1);
namespace Rector\Php80\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Php80\Rector\ClassMethod\SetStateToStaticRector\SetStateToStaticRectorTest
 */
final class SetStateToStaticRector extends AbstractRector implements MinPhpVersionInterface
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
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::STATIC_VISIBILITY_SET_STATE;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Adds static visibility to __set_state() methods', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function __set_state($properties) {

    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public static function __set_state($properties) {

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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $this->visibilityManipulator->makeStatic($node);
        return $node;
    }
    private function shouldSkip(ClassMethod $classMethod) : bool
    {
        if (!$this->isName($classMethod, MethodName::SET_STATE)) {
            return \true;
        }
        return $classMethod->isStatic();
    }
}
