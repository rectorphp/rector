<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v3;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220209\TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.3/Deprecation-84981-BackendUserAuthentication-simplelogDeprecated.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v3\BackendUserAuthenticationSimplelogRector\BackendUserAuthenticationSimplelogRectorTest
 */
final class BackendUserAuthenticationSimplelogRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Authentication\\BackendUserAuthentication'))) {
            return null;
        }
        if (!$this->isName($node->name, 'simplelog')) {
            return null;
        }
        /** @var Arg[] $currentArgs */
        $currentArgs = $node->args;
        $message = $this->valueResolver->getValue($currentArgs[0]->value);
        $extKey = isset($currentArgs[1]) ? $this->valueResolver->getValue($currentArgs[1]->value) : '';
        $details = ($extKey ? '[' . $extKey . '] ' : '') . $message;
        $args = [$this->nodeFactory->createArg(4), $this->nodeFactory->createArg(0), $currentArgs[2] ?? $this->nodeFactory->createArg(0), $this->nodeFactory->createArg($details), $this->nodeFactory->createArg([])];
        return $this->nodeFactory->createMethodCall($node->var, 'writelog', $args);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate the method BackendUserAuthentication->simplelog() to BackendUserAuthentication->writelog()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$someObject = GeneralUtility::makeInstance(TYPO3\CMS\Core\Authentication\BackendUserAuthentication::class);
$someObject->simplelog($message, $extKey, $error);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject = GeneralUtility::makeInstance(TYPO3\CMS\Core\Authentication\BackendUserAuthentication::class);
$someObject->writelog(4, 0, $error, 0, ($extKey ? '[' . $extKey . '] ' : '') . $message, []);
CODE_SAMPLE
)]);
    }
}
