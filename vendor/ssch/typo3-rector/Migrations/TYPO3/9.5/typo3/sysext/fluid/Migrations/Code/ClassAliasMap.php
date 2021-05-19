<?php

namespace RectorPrefix20210519;

return [
    // Base classes removed in TYPO3 v9
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractViewHelper' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractViewHelper',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractConditionViewHelper' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractConditionViewHelper',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\AbstractTagBasedViewHelper' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\AbstractTagBasedViewHelper',
    // Compiler/parser related aliases
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Compiler\\TemplateCompiler' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Compiler\\TemplateCompiler',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Parser\\InterceptorInterface' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Parser\\InterceptorInterface',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\NodeInterface' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\NodeInterface',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\AbstractNode' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Rendering\\RenderingContextInterface' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Rendering\\RenderingContextInterface',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperInterface' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperInterface',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\ChildNodeAccessInterface' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperInterface',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\CompilableInterface' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperInterface',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Facets\\PostParseInterface' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperInterface',
    // Fluid-specific errors
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Exception' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Exception' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\Exception\\InvalidVariableException' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\View\\Exception' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\View\\Exception',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\View\\Exception\\InvalidSectionException' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\View\\Exception\\InvalidSectionException',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\View\\Exception\\InvalidTemplateResourceException' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\View\\Exception\\InvalidTemplateResourceException',
    // Fluid variable containers, ViewHelpers, interfaces
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\RootNode' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\RootNode',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Parser\\SyntaxTree\\ViewHelperNode',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ArgumentDefinition' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ArgumentDefinition',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TemplateVariableContainer' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Variables\\StandardVariableProvider',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\ViewHelperVariableContainer',
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\Variables\\CmsVariableProvider' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\Variables\\StandardVariableProvider',
    // Semi API level classes; mainly used in unit tests
    'RectorPrefix20210519\\TYPO3\\CMS\\Fluid\\Core\\ViewHelper\\TagBuilder' => 'RectorPrefix20210519\\TYPO3Fluid\\Fluid\\Core\\ViewHelper\\TagBuilder',
];
