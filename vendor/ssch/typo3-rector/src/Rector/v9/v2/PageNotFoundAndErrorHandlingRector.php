<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v2;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Ternary;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Echo_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Throw_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.2/Deprecation-83883-PageNotFoundAndErrorHandlingInFrontend.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v2\PageNotFoundAndErrorHandlingRector\PageNotFoundAndErrorHandlingRectorTest
 */
final class PageNotFoundAndErrorHandlingRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, string>
     */
    private const MAP_METHODS = ['pageNotFoundAndExit' => 'pageNotFoundAction', 'pageUnavailableAndExit' => 'unavailableAction'];
    /**
     * @var string[]
     */
    private const METHODS = ['pageNotFoundAndExit', 'pageUnavailableAndExit', 'checkPageUnavailableHandler', 'pageUnavailableHandler', 'pageNotFoundHandler', 'pageErrorHandler'];
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    public function __construct(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Page Not Found And Error handling in Frontend', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
class SomeController extends ActionController
{
    public function unavailableAction(): void
    {
        $message = 'No entry found.';
        $GLOBALS['TSFE']->pageUnavailableAndExit($message);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Http\ImmediateResponseException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\Controller\ErrorController;
class SomeController extends ActionController
{
    public function unavailableAction(): void
    {
        $message = 'No entry found.';
        $response = GeneralUtility::makeInstance(ErrorController::class)->unavailableAction($GLOBALS['TYPO3_REQUEST'], $message);
        throw new ImmediateResponseException($response);
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isNames($node->name, self::METHODS)) {
            return null;
        }
        if ($this->isName($node->name, 'checkPageUnavailableHandler')) {
            return $this->refactorCheckPageUnavailableHandlerMethod();
        }
        if ($this->isNames($node->name, ['pageUnavailableHandler', 'pageNotFoundHandler', 'pageErrorHandler'])) {
            $newNode = $this->refactorPageErrorHandlerIfPossible($node);
            if (null !== $newNode) {
                $this->nodesToAddCollector->addNodeBeforeNode($newNode, $node);
                $this->removeNodeOrParentNode($node);
            }
            return null;
        }
        $responseNode = $this->createResponse($node);
        if (!$responseNode instanceof \PhpParser\Node) {
            return null;
        }
        $this->nodesToAddCollector->addNodeBeforeNode($responseNode, $node);
        $this->nodesToAddCollector->addNodeBeforeNode($this->throwException(), $node);
        $this->removeNodeOrParentNode($node);
        return $node;
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if ($this->typo3NodeResolver->isAnyMethodCallOnGlobals($methodCall, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            return \false;
        }
        return !$this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'));
    }
    private function createResponse(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        $methodCallName = $this->getName($methodCall->name);
        if (null === $methodCallName) {
            return null;
        }
        if (!\array_key_exists($methodCallName, self::MAP_METHODS)) {
            return null;
        }
        $arguments = [new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver::GLOBALS), new \PhpParser\Node\Scalar\String_('TYPO3_REQUEST'))];
        // Message
        $arguments[] = isset($methodCall->args[0]) ? $methodCall->args[0]->value : new \PhpParser\Node\Scalar\String_('');
        return new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable('response'), $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Frontend\\Controller\\ErrorController')]), self::MAP_METHODS[$methodCallName], $arguments)));
    }
    private function throwException() : \PhpParser\Node
    {
        return new \PhpParser\Node\Stmt\Throw_(new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name('TYPO3\\CMS\\Core\\Http\\ImmediateResponseException'), $this->nodeFactory->createArgs([new \PhpParser\Node\Expr\Variable('response')])));
    }
    private function refactorCheckPageUnavailableHandlerMethod() : \PhpParser\Node
    {
        $devIpMask = new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver::GLOBALS), new \PhpParser\Node\Scalar\String_('TYPO3_CONF_VARS')), new \PhpParser\Node\Scalar\String_('SYS')), new \PhpParser\Node\Scalar\String_('devIPmask'));
        $pageUnavailableHandling = new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver::GLOBALS), new \PhpParser\Node\Scalar\String_('TYPO3_CONF_VARS')), new \PhpParser\Node\Scalar\String_('FE')), new \PhpParser\Node\Scalar\String_('pageUnavailable_handling'));
        return new \PhpParser\Node\Expr\BinaryOp\BooleanAnd($pageUnavailableHandling, new \PhpParser\Node\Expr\BooleanNot($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'cmpIP', [$this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'getIndpEnv', [new \PhpParser\Node\Scalar\String_('REMOTE_ADDR')]), $devIpMask])));
    }
    private function refactorPageErrorHandlerIfPossible(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        if (!isset($methodCall->args[0])) {
            return null;
        }
        $code = $this->valueResolver->getValue($methodCall->args[0]->value);
        if (null === $code) {
            return null;
        }
        $message = null;
        if ('1' === (string) $code || \is_bool($code) || 'true' === \strtolower($code)) {
            $message = new \PhpParser\Node\Scalar\String_('The page did not exist or was inaccessible.');
            if (isset($methodCall->args[2])) {
                $reason = $methodCall->args[2]->value;
                $message = $this->nodeFactory->createConcat([$message, new \PhpParser\Node\Expr\Ternary($reason, $this->nodeFactory->createConcat([new \PhpParser\Node\Scalar\String_(' Reason: '), $reason]), new \PhpParser\Node\Scalar\String_(''))]);
            }
        }
        if ('' === $code) {
            $message = new \PhpParser\Node\Scalar\String_('Page cannot be found.');
            if (isset($methodCall->args[2])) {
                $reason = $methodCall->args[2]->value;
                $message = new \PhpParser\Node\Expr\Ternary($reason, $this->nodeFactory->createConcat([new \PhpParser\Node\Scalar\String_('Reason: '), $reason]), $message);
            }
        }
        if (null !== $message) {
            return new \PhpParser\Node\Stmt\Echo_([$this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Controller\\ErrorPageController')]), 'errorAction', ['Page Not Found', $message])]);
        }
        return null;
    }
    private function removeNodeOrParentNode(\PhpParser\Node $node) : void
    {
        try {
            $this->removeNode($node);
        } catch (\Rector\Core\Exception\ShouldNotHappenException $exception) {
            $this->removeNode($node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE));
        }
    }
}
