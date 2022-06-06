<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v7\v6;

use RectorPrefix20220606\PhpParser\BuilderHelpers;
use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.6.x/Breaking-72931-SearchFormControllerpi_list_browseresultsHasBeenRenamed.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v6\RenamePiListBrowserResultsRector\RenamePiListBrowserResultsRectorTest
 */
final class RenamePiListBrowserResultsRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node->var, new ObjectType('TYPO3\\CMS\\IndexedSearch\\Controller\\SearchFormController'))) {
            return null;
        }
        if (!$this->isName($node->name, 'pi_list_browseresults')) {
            return null;
        }
        return $this->process($node, 'renderPagination');
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Rename pi_list_browseresults calls to renderPagination', [new CodeSample('$this->pi_list_browseresults', '$this->renderPagination')]);
    }
    /**
     * @param string|string[] $newMethodNames
     * @return \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\ArrayDimFetch
     */
    private function process(MethodCall $methodCall, $newMethodNames)
    {
        if (\is_string($newMethodNames)) {
            $methodCall->name = new Identifier($newMethodNames);
            return $methodCall;
        }
        // special case for array dim fetch
        $methodCall->name = new Identifier($newMethodNames['name']);
        return new ArrayDimFetch($methodCall, BuilderHelpers::normalizeValue($newMethodNames['array_key']));
    }
}
