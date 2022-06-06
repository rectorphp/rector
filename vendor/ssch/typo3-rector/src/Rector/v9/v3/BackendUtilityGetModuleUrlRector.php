<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v3;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.3/Deprecation-85113-LegacyBackendModuleRoutingMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v3\BackendUtilityGetModuleUrlRector\BackendUtilityGetModuleUrlRectorTest
 */
final class BackendUtilityGetModuleUrlRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Backend\\Utility\\BackendUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'getModuleUrl')) {
            return null;
        }
        /** @var Arg[] $args */
        $args = $node->args;
        $firstArgument = \array_shift($args);
        if (!$firstArgument instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $secondArgument = \array_shift($args);
        return $this->createUriBuilderCall($firstArgument, $secondArgument);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate the method BackendUtility::getModuleUrl() to use UriBuilder API', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$moduleName = 'record_edit';
$params = ['pid' => 2];
$url = BackendUtility::getModuleUrl($moduleName, $params);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$moduleName = 'record_edit';
$params = ['pid' => 2];
$url = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute($moduleName, $params);
CODE_SAMPLE
)]);
    }
    private function createUriBuilderCall(\PhpParser\Node\Arg $firstArgument, ?\PhpParser\Node\Arg $secondArgument) : \PhpParser\Node\Expr\MethodCall
    {
        $buildUriArguments = [$firstArgument->value];
        if (null !== $secondArgument) {
            $buildUriArguments[] = $secondArgument->value;
        }
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Backend\\Routing\\UriBuilder')]), 'buildUriFromRoute', $buildUriArguments);
    }
}
