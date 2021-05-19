<?php

namespace RectorPrefix20210519;

return [
    // Base classes removed in TYPO3 v9
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractViewHelper' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractConditionViewHelper' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\AbstractConditionViewHelper::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractTagBasedViewHelper' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper::class,
    // Compiler/parser related aliases
    'TYPO3\\CMS\\Fluid\\Core\\Compiler\\TemplateCompiler' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler::class,
    'TYPO3\\CMS\\Fluid\\Core\\Parser\\InterceptorInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface::class,
    'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\NodeInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface::class,
    'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\AbstractNode' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode::class,
    'TYPO3\\CMS\\Fluid\\Core\\Rendering\\RenderingContextInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\ChildNodeAccessInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\CompilableInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\PostParseInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface::class,
    // Fluid-specific errors
    'TYPO3\\CMS\\Fluid\\Core\\Exception' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Exception::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Exception' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\Exception::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Exception\\InvalidVariableException' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Exception::class,
    'TYPO3\\CMS\\Fluid\\View\\Exception' => \RectorPrefix20210519\TYPO3Fluid\Fluid\View\Exception::class,
    'TYPO3\\CMS\\Fluid\\View\\Exception\\InvalidSectionException' => \RectorPrefix20210519\TYPO3Fluid\Fluid\View\Exception\InvalidSectionException::class,
    'TYPO3\\CMS\\Fluid\\View\\Exception\\InvalidTemplateResourceException' => \RectorPrefix20210519\TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException::class,
    // Fluid variable containers, ViewHelpers, interfaces
    'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\RootNode' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode::class,
    'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ArgumentDefinition' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ArgumentDefinition::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TemplateVariableContainer' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider::class,
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer::class,
    'TYPO3\\CMS\\Fluid\\Core\\Variables\\CmsVariableProvider' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider::class,
    // Semi API level classes; mainly used in unit tests
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TagBuilder' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder::class,
];
