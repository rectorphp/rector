<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#form
 * @see \Rector\Symfony\Tests\Rector\New_\PropertyPathMapperToDataMapperRector\PropertyPathMapperToDataMapperRectorTest
 */
final class PropertyPathMapperToDataMapperRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate from PropertyPathMapper to DataMapper and PropertyPathAccessor', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

class SomeClass
{
    public function run()
    {
        return new PropertyPathMapper();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\Extension\Core\DataMapper\PropertyPathMapper;

class SomeClass
{
    public function run()
    {
        return new \Symfony\Component\Form\Extension\Core\DataMapper\DataMapper(new \Symfony\Component\Form\Extension\Core\DataAccessor\PropertyPathAccessor());
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
        return [\PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        return $this->generateNewInstances($node);
    }
    private function shouldSkip(\PhpParser\Node\Expr\New_ $new) : bool
    {
        if (!$new->class instanceof \PhpParser\Node\Name) {
            return \true;
        }
        return !$this->isName($new->class, 'Symfony\\Component\\Form\\Extension\\Core\\DataMapper\\PropertyPathMapper');
    }
    private function generateNewInstances(\PhpParser\Node\Expr\New_ $new) : \PhpParser\Node\Expr\New_
    {
        $arguments = [];
        if (isset($new->args[0])) {
            $arguments = [$new->args[0]];
        }
        $new = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\Form\\Extension\\Core\\DataAccessor\\PropertyPathAccessor'), $arguments);
        return new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\Form\\Extension\\Core\\DataMapper\\DataMapper'), [$this->nodeFactory->createArg($new)]);
    }
}
