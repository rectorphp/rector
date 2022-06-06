<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\BinaryOp\BitwiseAnd;
use PhpParser\Node\Expr\BinaryOp\BooleanOr;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-85285-DeprecatedSystemConstants.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\ConstantsToEnvironmentApiCallRector\ConstantsToEnvironmentApiCallRectorTest
 */
final class ConstantsToEnvironmentApiCallRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private const ALLOWED_NAMES = ['TYPO3_REQUESTTYPE_CLI', 'TYPO3_REQUESTTYPE'];
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns defined constant to static method call of new Environment API.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample('PATH_thisScript;', 'Environment::getCurrentScript();')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\ConstFetch::class, \PhpParser\Node\Expr\BinaryOp\BitwiseAnd::class];
    }
    /**
     * @param ConstFetch|BitwiseAnd $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\ConstFetch) {
            return $this->refactorConstants($node);
        }
        if (!$node->left instanceof \PhpParser\Node\Expr\ConstFetch || !$node->right instanceof \PhpParser\Node\Expr\ConstFetch) {
            return null;
        }
        if (!$this->isNames($node->left, self::ALLOWED_NAMES) || !$this->isNames($node->right, self::ALLOWED_NAMES)) {
            return null;
        }
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'isCli');
    }
    private function refactorConstants(\PhpParser\Node\Expr\ConstFetch $node) : ?\PhpParser\Node
    {
        $constantName = $this->getName($node);
        if (null === $constantName) {
            return null;
        }
        if (!\in_array($constantName, ['PATH_thisScript', 'PATH_site', 'PATH_typo3', 'PATH_typo3conf', 'TYPO3_OS'], \false)) {
            return null;
        }
        $property = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Property::class);
        if (null !== $property) {
            return null;
        }
        $constant = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Const_::class);
        if (null !== $constant) {
            return null;
        }
        if ('PATH_thisScript' === $constantName) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'getCurrentScript');
        }
        if ('PATH_site' === $constantName) {
            return new \PhpParser\Node\Expr\BinaryOp\Concat($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'getPublicPath'), new \PhpParser\Node\Scalar\String_('/'));
        }
        if ('PATH_typo3' === $constantName) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'getBackendPath');
        }
        if ('PATH_typo3conf' === $constantName) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'getLegacyConfigPath');
        }
        return new \PhpParser\Node\Expr\BinaryOp\BooleanOr($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'isUnix'), $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Core\\Environment', 'isWindows'));
    }
}
