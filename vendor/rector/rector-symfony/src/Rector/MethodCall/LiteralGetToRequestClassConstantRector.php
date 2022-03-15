<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer;
use Rector\Symfony\ValueObject\ConstantMap\SymfonyRequestConstantMap;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\LiteralGetToRequestClassConstantRector\LiteralGetToRequestClassConstantRectorTest
 */
final class LiteralGetToRequestClassConstantRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer
     */
    private $literalCallLikeConstFetchReplacer;
    public function __construct(\Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer $literalCallLikeConstFetchReplacer)
    {
        $this->literalCallLikeConstFetchReplacer = $literalCallLikeConstFetchReplacer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace "GET" string by Symfony Request object class constants', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilderInterface;

final class SomeClass
{
    public function detail(FormBuilderInterface $formBuilder)
    {
        $formBuilder->setMethod('GET');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\FormBuilderInterface;

final class SomeClass
{
    public function detail(FormBuilderInterface $formBuilder)
    {
        $formBuilder->setMethod(\Symfony\Component\HttpFoundation\Request::GET);
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
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\StaticCall) {
            return $this->refactorStaticCall($node);
        }
        if ($this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\HttpKernel\\Client'))) {
            return $this->refactorClientMethodCall($node);
        }
        if (!$this->isObjectType($node->var, new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\FormBuilderInterface'))) {
            return null;
        }
        if (!$this->isName($node->name, 'setMethod')) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($node, 0, 'Symfony\\Component\\HttpFoundation\\Request', \Rector\Symfony\ValueObject\ConstantMap\SymfonyRequestConstantMap::METHOD_TO_CONST);
    }
    /**
     * @return \PhpParser\Node\Expr\StaticCall|null
     */
    private function refactorStaticCall(\PhpParser\Node\Expr\StaticCall $staticCall)
    {
        if (!$this->isObjectType($staticCall->class, new \PHPStan\Type\ObjectType('Symfony\\Component\\HttpFoundation\\Request'))) {
            return null;
        }
        if (!$this->isName($staticCall->name, 'create')) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($staticCall, 1, 'Symfony\\Component\\HttpFoundation\\Request', \Rector\Symfony\ValueObject\ConstantMap\SymfonyRequestConstantMap::METHOD_TO_CONST);
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function refactorClientMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall)
    {
        if (!$this->isName($methodCall->name, 'request')) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 0, 'Symfony\\Component\\HttpFoundation\\Request', \Rector\Symfony\ValueObject\ConstantMap\SymfonyRequestConstantMap::METHOD_TO_CONST);
    }
}
