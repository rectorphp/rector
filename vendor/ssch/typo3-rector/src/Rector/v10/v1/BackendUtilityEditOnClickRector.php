<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v1;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.1/Deprecation-88787-BackendUtilityEditOnClick.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v1\BackendUtilityEditOnClickRector\BackendUtilityEditOnClickRectorTest
 */
final class BackendUtilityEditOnClickRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Backend\\Utility\\BackendUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'editOnClick')) {
            return null;
        }
        $firstArgument = $node->args[0];
        return new Concat($this->createUriBuilderCall($firstArgument), $this->createRequestUriCall());
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate the method BackendUtility::editOnClick() to use UriBuilder API', [new CodeSample(<<<'CODE_SAMPLE'
$pid = 2;
$params = '&edit[pages][' . $pid . ']=new&returnNewPageId=1';
$url = BackendUtility::editOnClick($params);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$pid = 2;
$params = '&edit[pages][' . $pid . ']=new&returnNewPageId=1';
$url = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('record_edit') . $params . '&returnUrl=' . rawurlencode(GeneralUtility::getIndpEnv('REQUEST_URI'));;
CODE_SAMPLE
)]);
    }
    private function createUriBuilderCall(Arg $firstArgument) : Concat
    {
        return new Concat(new Concat($this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Backend\\Routing\\UriBuilder')]), 'buildUriFromRoute', [$this->nodeFactory->createArg('record_edit')]), $firstArgument->value), new String_('&returnUrl='));
    }
    private function createRequestUriCall() : FuncCall
    {
        return new FuncCall(new Name('rawurlencode'), [$this->nodeFactory->createArg($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'getIndpEnv', [$this->nodeFactory->createArg('REQUEST_URI')]))]);
    }
}
