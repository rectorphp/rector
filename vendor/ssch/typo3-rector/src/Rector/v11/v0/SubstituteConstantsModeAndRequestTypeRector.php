<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v11\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Equal;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Identical;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\FilesFinder;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.0/Deprecation-92947-DeprecateTYPO3_MODEAndTYPO3_REQUESTTYPEConstants.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v0\SubstituteConstantsModeAndRequestTypeRector\SubstituteConstantsModeAndRequestTypeRectorTest
 */
final class SubstituteConstantsModeAndRequestTypeRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\FilesFinder
     */
    private $filesFinder;
    public function __construct(FilesFinder $filesFinder)
    {
        $this->filesFinder = $filesFinder;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ConstFetch::class, FuncCall::class];
    }
    /**
     * @param ConstFetch|FuncCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        $fileInfo = $this->file->getSmartFileInfo();
        if ($node instanceof FuncCall && $this->isName($node, 'defined')) {
            return $this->refactorProbablySecurityGate($node);
        }
        if ($this->filesFinder->isExtLocalConf($fileInfo) || $this->filesFinder->isExtTables($fileInfo)) {
            return null;
        }
        if (!$this->isNames($node, ['TYPO3_MODE', 'TYPO3_REQUESTTYPE_FE', 'TYPO3_REQUESTTYPE_BE'])) {
            return null;
        }
        $arguments = [new ArrayDimFetch(new Variable(Typo3NodeResolver::GLOBALS), new String_('TYPO3_REQUEST'))];
        if ($this->isName($node, 'TYPO3_REQUESTTYPE_FE')) {
            return $this->createIsFrontendCall($arguments);
        }
        if ($this->isName($node, 'TYPO3_REQUESTTYPE_BE')) {
            return $this->createIsBackendCall($arguments);
        }
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Identical && !$parentNode instanceof Equal) {
            return null;
        }
        $type = $parentNode->left === $node ? $this->valueResolver->getValue($parentNode->right) : $this->valueResolver->getValue($parentNode->left);
        if (null === $type || !\in_array($type, ['FE', 'BE'], \true)) {
            return null;
        }
        $this->removeNode($parentNode->left === $node ? $parentNode->right : $parentNode->left);
        if ('BE' === $type) {
            return $this->createIsBackendCall($arguments);
        }
        return $this->createIsFrontendCall($arguments);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Substitute TYPO3_MODE and TYPO3_REQUESTTYPE constants', [new CodeSample(<<<'CODE_SAMPLE'
defined('TYPO3_MODE') or die();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
defined('TYPO3') or die();
CODE_SAMPLE
)]);
    }
    private function refactorProbablySecurityGate(Node $node) : ?Node
    {
        if (!$node instanceof FuncCall) {
            return null;
        }
        $firstArgument = $node->args[0]->value;
        if (!$firstArgument instanceof String_) {
            return null;
        }
        if (!$this->valueResolver->isValue($firstArgument, 'TYPO3_MODE')) {
            return null;
        }
        $node->args[0]->value = new String_('TYPO3');
        return $node;
    }
    /**
     * @param Expr[] $arguments
     */
    private function createIsBackendCall(array $arguments) : MethodCall
    {
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Http\\ApplicationType', 'fromRequest', $arguments), 'isBackend');
    }
    /**
     * @param Expr[] $arguments
     */
    private function createIsFrontendCall(array $arguments) : MethodCall
    {
        return $this->nodeFactory->createMethodCall($this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Http\\ApplicationType', 'fromRequest', $arguments), 'isFrontend');
    }
}
