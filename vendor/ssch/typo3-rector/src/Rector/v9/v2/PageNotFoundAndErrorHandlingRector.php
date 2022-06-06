<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v9\v2;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BooleanNot;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Expr\Ternary;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Echo_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\Throw_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.2/Deprecation-83883-PageNotFoundAndErrorHandlingInFrontend.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v2\PageNotFoundAndErrorHandlingRector\PageNotFoundAndErrorHandlingRectorTest
 */
final class PageNotFoundAndErrorHandlingRector extends AbstractRector
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
    public function __construct(Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Page Not Found And Error handling in Frontend', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
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
        if (!$responseNode instanceof Node) {
            return null;
        }
        $this->nodesToAddCollector->addNodeBeforeNode($responseNode, $node);
        $this->nodesToAddCollector->addNodeBeforeNode($this->throwException(), $node);
        $this->removeNodeOrParentNode($node);
        return $node;
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if ($this->typo3NodeResolver->isAnyMethodCallOnGlobals($methodCall, Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            return \false;
        }
        return !$this->isObjectType($methodCall->var, new ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'));
    }
    private function createResponse(MethodCall $methodCall) : ?Node
    {
        $methodCallName = $this->getName($methodCall->name);
        if (null === $methodCallName) {
            return null;
        }
        if (!\array_key_exists($methodCallName, self::MAP_METHODS)) {
            return null;
        }
        $arguments = [new ArrayDimFetch(new Variable(Typo3NodeResolver::GLOBALS), new String_('TYPO3_REQUEST'))];
        // Message
        $arguments[] = isset($methodCall->args[0]) ? $methodCall->args[0]->value : new String_('');
        return new Expression(new Assign(new Variable('response'), $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Frontend\\Controller\\ErrorController')]), self::MAP_METHODS[$methodCallName], $arguments)));
    }
    private function throwException() : Node
    {
        return new Throw_(new New_(new Name('TYPO3\\CMS\\Core\\Http\\ImmediateResponseException'), $this->nodeFactory->createArgs([new Variable('response')])));
    }
    private function refactorCheckPageUnavailableHandlerMethod() : Node
    {
        $devIpMask = new ArrayDimFetch(new ArrayDimFetch(new ArrayDimFetch(new Variable(Typo3NodeResolver::GLOBALS), new String_('TYPO3_CONF_VARS')), new String_('SYS')), new String_('devIPmask'));
        $pageUnavailableHandling = new ArrayDimFetch(new ArrayDimFetch(new ArrayDimFetch(new Variable(Typo3NodeResolver::GLOBALS), new String_('TYPO3_CONF_VARS')), new String_('FE')), new String_('pageUnavailable_handling'));
        return new BooleanAnd($pageUnavailableHandling, new BooleanNot($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'cmpIP', [$this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'getIndpEnv', [new String_('REMOTE_ADDR')]), $devIpMask])));
    }
    private function refactorPageErrorHandlerIfPossible(MethodCall $methodCall) : ?Node
    {
        if (!isset($methodCall->args[0])) {
            return null;
        }
        $code = $this->valueResolver->getValue($methodCall->args[0]->value);
        if (null === $code) {
            return null;
        }
        $message = null;
        if ('1' === (string) $code || \is_bool($code) || 'true' === \strtolower((string) $code)) {
            $message = new String_('The page did not exist or was inaccessible.');
            if (isset($methodCall->args[2])) {
                $reason = $methodCall->args[2]->value;
                $message = $this->nodeFactory->createConcat([$message, new Ternary($reason, $this->nodeFactory->createConcat([new String_(' Reason: '), $reason]), new String_(''))]);
            }
        }
        if ('' === $code) {
            $message = new String_('Page cannot be found.');
            if (isset($methodCall->args[2])) {
                $reason = $methodCall->args[2]->value;
                $message = new Ternary($reason, $this->nodeFactory->createConcat([new String_('Reason: '), $reason]), $message);
            }
        }
        if (null !== $message) {
            return new Echo_([$this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Controller\\ErrorPageController')]), 'errorAction', ['Page Not Found', $message])]);
        }
        return null;
    }
    private function removeNodeOrParentNode(Node $node) : void
    {
        try {
            $this->removeNode($node);
        } catch (ShouldNotHappenException $exception) {
            $this->removeNode($node->getAttribute(AttributeKey::PARENT_NODE));
        }
    }
}
