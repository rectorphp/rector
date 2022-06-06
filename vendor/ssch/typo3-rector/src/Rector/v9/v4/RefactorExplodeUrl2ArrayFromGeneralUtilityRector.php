<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v4;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Arg;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85801-GeneralUtilityexplodeUrl2Array-2ndMethodArgument.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\RefactorExplodeUrl2ArrayFromGeneralUtilityRector\RefactorExplodeUrl2ArrayFromGeneralUtilityRectorTest
 */
final class RefactorExplodeUrl2ArrayFromGeneralUtilityRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Assign::class];
    }
    /**
     * @param Assign $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->expr instanceof StaticCall && !$node->expr instanceof MethodCall) {
            return null;
        }
        /** @var StaticCall|MethodCall $call */
        $call = $node->expr;
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($call, new ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($call->name, 'explodeUrl2Array')) {
            return null;
        }
        $arguments = $call->args;
        if (\count($arguments) <= 1) {
            return null;
        }
        /** @var Arg $lastArgument */
        $lastArgument = \array_pop($arguments);
        if ($this->valueResolver->isFalse($lastArgument->value)) {
            $call->args = $arguments;
            return null;
        }
        return $this->nodeFactory->createFuncCall('parse_str', [$arguments[0], $node->var]);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove second argument of GeneralUtility::explodeUrl2Array if it is false or just use function parse_str if it is true', [new CodeSample(<<<'CODE_SAMPLE'
$variable = GeneralUtility::explodeUrl2Array('https://www.domain.com', true);
$variable2 = GeneralUtility::explodeUrl2Array('https://www.domain.com', false);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
parse_str('https://www.domain.com', $variable);
$variable2 = GeneralUtility::explodeUrl2Array('https://www.domain.com');
CODE_SAMPLE
)]);
    }
}
