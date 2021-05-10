<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/nette/utils/commit/75abe7c6aa472fd023aa49ba1a4d6c6eca0eaaa6
 * @see https://github.com/nette/utils/issues/88
 *
 * @see \Rector\Nette\Tests\Rector\MethodCall\MagicHtmlCallToAppendAttributeRector\MagicHtmlCallToAppendAttributeRectorTest
 */
final class MagicHtmlCallToAppendAttributeRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change magic addClass() etc. calls on Html to explicit methods', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Nette\Utils\Html;

final class SomeClass
{
    public function run()
    {
        $html = Html::el();
        $html->setClass('first');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Nette\Utils\Html;

final class SomeClass
{
    public function run()
    {
        $html = Html::el();
        $html->appendAttribute('class', 'first');
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
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Nette\\Utils\\Html'))) {
            return null;
        }
        // @todo posibly extends by more common names
        if ($this->isName($node->name, 'setClass')) {
            $node->name = new \PhpParser\Node\Identifier('appendAttribute');
            $args = \array_merge([new \PhpParser\Node\Arg(new \PhpParser\Node\Scalar\String_('class'))], $node->args);
            $node->args = $args;
            return $node;
        }
        return null;
    }
}
