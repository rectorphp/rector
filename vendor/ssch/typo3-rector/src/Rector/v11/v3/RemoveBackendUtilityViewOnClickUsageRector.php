<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v3;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Console\Output\RectorOutputStyle;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.3/Deprecation-91806-BackendUtilityViewOnClick.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v3\RemoveBackendUtilityViewOnClickUsageRector\RemoveBackendUtilityViewOnClickUsageRectorTest
 */
final class RemoveBackendUtilityViewOnClickUsageRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const MESSAGE = 'Rector changed the BackendUtility::viewOnClick call, but further argument resolving is necessary. See Deprecation-91806-BackendUtilityViewOnClick.html and Important-91123-AvoidUsingBackendUtilityViewOnClick.html';
    /**
     * @readonly
     * @var \Rector\Core\Console\Output\RectorOutputStyle
     */
    private $rectorOutputStyle;
    public function __construct(\Rector\Core\Console\Output\RectorOutputStyle $rectorOutputStyle)
    {
        $this->rectorOutputStyle = $rectorOutputStyle;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param Node\Expr\StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (isset($node->args[6])) {
            $varName = $this->nodeNameResolver->getName($node->args[6]->value) ?? '';
            $dispatchArgs = new \PhpParser\Node\Expr\Array_([new \PhpParser\Node\Expr\ArrayItem(new \PhpParser\Node\Expr\Variable($varName), $this->nodeFactory->createClassConstFetch('TYPO3\\CMS\\Backend\\Routing\\PreviewUriBuilder', 'OPTION_SWITCH_FOCUS'))]);
        }
        $createCallArgs = [$node->args[0], $node->args[4] ?? null];
        $createCallArgs = \array_filter($createCallArgs);
        $createCall = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Backend\\Routing\\PreviewUriBuilder', 'create', $createCallArgs);
        $chainCalls = $createCall;
        if (isset($node->args[2])) {
            $chainCalls = $this->nodeFactory->createMethodCall($createCall, 'withRootLine', [$node->args[2]]);
        }
        if (isset($node->args[3])) {
            $chainCalls = $this->nodeFactory->createMethodCall($chainCalls, 'withSection', [$node->args[3]]);
        }
        if (isset($node->args[5])) {
            $chainCalls = $this->nodeFactory->createMethodCall($chainCalls, 'withAdditionalQueryParameters', [$node->args[5]]);
        }
        $this->rectorOutputStyle->warning(self::MESSAGE);
        return $this->nodeFactory->createMethodCall($chainCalls, 'buildDispatcherDataAttributes', isset($dispatchArgs) ? [$dispatchArgs->items] : []);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Resolve usages of BackendUtility::viewOnClick to new method', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$onclick = BackendUtility::viewOnClick(
    $pageId, $backPath, $rootLine, $section,
    $viewUri, $getVars, $switchFocus
);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$onclick = PreviewUriBuilder::create($pageId, $viewUri)
    ->withRootLine($rootLine)
    ->withSection($section)
    ->withAdditionalQueryParameters($getVars)
    ->buildDispatcherDataAttributes([
        PreviewUriBuilder::OPTION_SWITCH_FOCUS => $switchFocus,
    ]);
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\StaticCall $staticCall) : bool
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($staticCall, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Utility\\BackendUtility'))) {
            return \true;
        }
        return !$this->isName($staticCall->name, 'viewOnClick');
    }
}
