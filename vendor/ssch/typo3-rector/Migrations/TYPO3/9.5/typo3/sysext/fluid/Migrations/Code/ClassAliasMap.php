<?php

namespace RectorPrefix20220418;

return [
    // Base classes removed in TYPO3 v9
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractViewHelper' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractViewHelper',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractConditionViewHelper' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractConditionViewHelper',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractTagBasedViewHelper' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractTagBasedViewHelper',
    // Compiler/parser related aliases
    'TYPO3\\CMS\\Fluid\\Core\\Compiler\\TemplateCompiler' => 'TYPO3Fluid\\Fluid\\Core\\Compiler\\TemplateCompiler',
    'TYPO3\\CMS\\Fluid\\Core\\Parser\\InterceptorInterface' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\InterceptorInterface',
    'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\NodeInterface' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\NodeInterface',
    'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\AbstractNode' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode',
    'TYPO3\\CMS\\Fluid\\Core\\Rendering\\RenderingContextInterface' => 'TYPO3Fluid\\Fluid\\Core\\Rendering\\RenderingContextInterface',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperInterface' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperInterface',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\ChildNodeAccessInterface' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperInterface',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\CompilableInterface' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperInterface',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\PostParseInterface' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperInterface',
    // Fluid-specific errors
    'TYPO3\\CMS\\Fluid\\Core\\Exception' => 'TYPO3Fluid\\Fluid\\Core\\Exception',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Exception' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\Exception',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Exception\\InvalidVariableException' => 'TYPO3Fluid\\Fluid\\Core\\Exception',
    'TYPO3\\CMS\\Fluid\\View\\Exception' => 'TYPO3Fluid\\Fluid\\View\\Exception',
    'TYPO3\\CMS\\Fluid\\View\\Exception\\InvalidSectionException' => 'TYPO3Fluid\\Fluid\\View\\Exception\\InvalidSectionException',
    'TYPO3\\CMS\\Fluid\\View\\Exception\\InvalidTemplateResourceException' => 'TYPO3Fluid\\Fluid\\View\\Exception\\InvalidTemplateResourceException',
    // Fluid variable containers, ViewHelpers, interfaces
    'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\RootNode' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\RootNode',
    'TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode' => 'TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ArgumentDefinition' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ArgumentDefinition',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TemplateVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\Variables\\StandardVariableProvider',
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
    'TYPO3\\CMS\\Fluid\\Core\\Variables\\CmsVariableProvider' => 'TYPO3Fluid\\Fluid\\Core\\Variables\\StandardVariableProvider',
    // Semi API level classes; mainly used in unit tests
    'TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TagBuilder' => 'TYPO3Fluid\\Fluid\\Core\\ViewHelper\\TagBuilder',
];
