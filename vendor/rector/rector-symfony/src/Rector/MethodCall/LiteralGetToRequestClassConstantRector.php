<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Symfony\Rector\MethodCall;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Reflection\ReflectionProvider;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer;
use RectorPrefix20220606\Rector\Symfony\ValueObject\ConstantMap\SymfonyRequestConstantMap;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Rector\MethodCall\LiteralGetToRequestClassConstantRector\LiteralGetToRequestClassConstantRectorTest
 */
final class LiteralGetToRequestClassConstantRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Symfony\NodeAnalyzer\LiteralCallLikeConstFetchReplacer
     */
    private $literalCallLikeConstFetchReplacer;
    /**
     * @readonly
     * @var \PHPStan\Reflection\ReflectionProvider
     */
    private $reflectionProvider;
    public function __construct(LiteralCallLikeConstFetchReplacer $literalCallLikeConstFetchReplacer, ReflectionProvider $reflectionProvider)
    {
        $this->literalCallLikeConstFetchReplacer = $literalCallLikeConstFetchReplacer;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace "GET" string by Symfony Request object class constants', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof StaticCall) {
            return $this->refactorStaticCall($node);
        }
        // for client, the transitional dependency to browser-kit might be missing and cause fatal error on PHPStan reflection
        // in most cases that should be skipped, @see https://github.com/rectorphp/rector/issues/7135
        if ($this->reflectionProvider->hasClass('Symfony\\Component\\BrowserKit\\AbstractBrowser') && $this->isObjectType($node->var, new ObjectType('Symfony\\Component\\HttpKernel\\Client'))) {
            return $this->refactorClientMethodCall($node);
        }
        if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\Form\\FormBuilderInterface'))) {
            return null;
        }
        if (!$this->isName($node->name, 'setMethod')) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($node, 0, 'Symfony\\Component\\HttpFoundation\\Request', SymfonyRequestConstantMap::METHOD_TO_CONST);
    }
    /**
     * @return \PhpParser\Node\Expr\StaticCall|null
     */
    private function refactorStaticCall(StaticCall $staticCall)
    {
        if (!$this->isObjectType($staticCall->class, new ObjectType('Symfony\\Component\\HttpFoundation\\Request'))) {
            return null;
        }
        if (!$this->isName($staticCall->name, 'create')) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($staticCall, 1, 'Symfony\\Component\\HttpFoundation\\Request', SymfonyRequestConstantMap::METHOD_TO_CONST);
    }
    /**
     * @return \PhpParser\Node\Expr\MethodCall|null
     */
    private function refactorClientMethodCall(MethodCall $methodCall)
    {
        if (!$this->isName($methodCall->name, 'request')) {
            return null;
        }
        return $this->literalCallLikeConstFetchReplacer->replaceArgOnPosition($methodCall, 0, 'Symfony\\Component\\HttpFoundation\\Request', SymfonyRequestConstantMap::METHOD_TO_CONST);
    }
}
