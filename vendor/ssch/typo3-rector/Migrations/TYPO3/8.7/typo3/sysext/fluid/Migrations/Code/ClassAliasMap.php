<?php

namespace RectorPrefix20210519;

return [
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Compiler\\TemplateCompiler' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Compiler\TemplateCompiler::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Parser\\InterceptorInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Parser\InterceptorInterface::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\NodeInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\NodeInterface::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\AbstractNode' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Rendering\\RenderingContextInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\ChildNodeAccessInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\CompilableInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\PostParseInterface' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperInterface::class,
    // Fluid-specific errors
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Exception' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Exception' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Exception\\InvalidVariableException' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\View\\Exception' => \RectorPrefix20210519\TYPO3Fluid\Fluid\View\Exception::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\View\\Exception\\InvalidSectionException' => \RectorPrefix20210519\TYPO3Fluid\Fluid\View\Exception\InvalidSectionException::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\View\\Exception\\InvalidTemplateResourceException' => \RectorPrefix20210519\TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException::class,
    // Fluid variable containers, ViewHelpers, interfaces
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\RootNode' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\RootNode::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Parser\SyntaxTree\ViewHelperNode::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TemplateVariableContainer' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider::class,
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\ViewHelperVariableContainer::class,
    // Semi API level classes; mainly used in unit tests
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TagBuilder' => \RectorPrefix20210519\TYPO3Fluid\Fluid\Core\ViewHelper\TagBuilder::class,
];
