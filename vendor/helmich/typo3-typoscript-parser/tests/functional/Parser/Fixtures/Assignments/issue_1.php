<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Comment;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NopStatement;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Statement;
return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('config.tx_extbase', 'config.tx_extbase'), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NopStatement(2), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('config.tx_extbase.view', 'view'), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Comment('# Configure where to look for widget templates', 4), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('config.tx_extbase.view.widget', 'widget'), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('RectorPrefix20210518\\config.tx_extbase.view.widget.TYPO3\\CMS\\Fluid\\ViewHelpers\\Widget\\PaginateViewHelper', 'RectorPrefix20210518\\TYPO3\\CMS\\Fluid\\ViewHelpers\\Widget\\PaginateViewHelper'), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('config.tx_extbase.view.widget.TYPO3\\CMS\\Fluid\\ViewHelpers\\Widget\\PaginateViewHelper.templateRootPath', 'templateRootPath'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('EXT:ext_key/Resources/Private/Templates'), 7)], 6)], 5)], 3)], 2)];
