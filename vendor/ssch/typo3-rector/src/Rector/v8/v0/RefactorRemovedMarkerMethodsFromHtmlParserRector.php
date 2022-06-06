<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Identifier;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-72384-RemovedDeprecatedCodeFromHtmlParser.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\CoreRector\Html\RefactorRemovedMarkerMethodsFromHtmlParserRectorTest
 */
final class RefactorRemovedMarkerMethodsFromHtmlParserRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const MOVED_METHODS_TO_MARKER_BASED_TEMPLATES = ['getSubpart', 'substituteSubpart', 'substituteSubpartArray', 'substituteMarker', 'substituteMarkerArray', 'substituteMarkerAndSubpartArrayRecursive'];
    /**
     * @var string
     */
    private const RENAMED_METHOD = 'XHTML_clean';
    /**
     * @var string[]
     */
    private const REMOVED_METHODS = ['processTag', 'processContent'];
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param StaticCall|MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Html\\HtmlParser'))) {
            return null;
        }
        if ($this->shouldSkip($node)) {
            return null;
        }
        $migratedNode = $this->migrateMethodsToMarkerBasedTemplateService($node);
        if (null !== $migratedNode) {
            return $migratedNode;
        }
        $this->renameMethod($node);
        $this->removeMethods($node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor removed Marker-related methods from HtmlParser.', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Html\HtmlParser;

final class HtmlParserMarkerRendererMethods
{
    public function doSomething(): void
    {
        $template = '';
        $markerArray = [];
        $subpartArray = [];
        $htmlparser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(HtmlParser::class);
        $template = $htmlparser->getSubpart($this->config['templateFile'], '###TEMPLATE###');
        $html = $htmlparser->substituteSubpart($html, '###ADDITONAL_KEYWORD###', '');
        $html2 = $htmlparser->substituteSubpartArray($html2, []);

        $html3 = $htmlparser->processTag($value, $conf, $endTag, $protected = 0);
        $html4 = $htmlparser->processContent($value, $dir, $conf);

        $content = $htmlparser->substituteMarker($content, $marker, $markContent);
        $content .= $htmlparser->substituteMarkerArray($content, $markContentArray, $wrap, $uppercase, $deleteUnused);
        $content .= $htmlparser->substituteMarkerAndSubpartArrayRecursive($content, $markersAndSubparts, $wrap, $uppercase, $deleteUnused);
        $content = $htmlparser->XHTML_clean($content);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Html\HtmlParser;

final class HtmlParserMarkerRendererMethods
{
    public function doSomething(): void
    {
        $template = '';
        $markerArray = [];
        $subpartArray = [];
        $htmlparser = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(HtmlParser::class);
        $template = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\MarkerBasedTemplateService::class)->getSubpart($this->config['templateFile'], '###TEMPLATE###');
        $html = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\MarkerBasedTemplateService::class)->substituteSubpart($html, '###ADDITONAL_KEYWORD###', '');
        $html2 = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\MarkerBasedTemplateService::class)->substituteSubpartArray($html2, []);

        $content = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\MarkerBasedTemplateService::class)->substituteMarker($content, $marker, $markContent);
        $content .= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\MarkerBasedTemplateService::class)->substituteMarkerArray($content, $markContentArray, $wrap, $uppercase, $deleteUnused);
        $content .= \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Service\MarkerBasedTemplateService::class)->substituteMarkerAndSubpartArrayRecursive($content, $markersAndSubparts, $wrap, $uppercase, $deleteUnused);
        $content = $htmlparser->HTMLcleaner($content);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    public function removeMethods($call) : void
    {
        if ($this->isNames($call->name, self::REMOVED_METHODS)) {
            $methodName = $this->getName($call->name);
            if (null !== $methodName) {
                try {
                    $this->removeNode($call);
                } catch (ShouldNotHappenException $exception) {
                    $parentNode = $call->getAttribute(AttributeKey::PARENT_NODE);
                    $this->removeNode($parentNode);
                }
            }
        }
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    public function renameMethod($call) : void
    {
        if ($this->isName($call->name, self::RENAMED_METHOD)) {
            $methodName = $this->getName($call->name);
            if (null !== $methodName) {
                $call->name = new Identifier('HTMLcleaner');
            }
        }
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    private function migrateMethodsToMarkerBasedTemplateService($call) : ?Node
    {
        if ($this->isNames($call->name, self::MOVED_METHODS_TO_MARKER_BASED_TEMPLATES)) {
            $methodName = $this->getName($call->name);
            if (null !== $methodName) {
                $classConstant = $this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Service\\MarkerBasedTemplateService');
                $staticCall = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$classConstant]);
                return $this->nodeFactory->createMethodCall($staticCall, $methodName, $call->args);
            }
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Expr\MethodCall $call
     */
    private function shouldSkip($call) : bool
    {
        $skip = \false;
        if (!$this->isNames($call->name, self::MOVED_METHODS_TO_MARKER_BASED_TEMPLATES) && !$this->isNames($call->name, self::REMOVED_METHODS) && !$this->isName($call->name, self::RENAMED_METHOD)) {
            $skip = \true;
        }
        return $skip;
    }
}
