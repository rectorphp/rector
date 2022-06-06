<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v7;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.7/Deprecation-80053-ExtbaseCLIConsoleOutputDifferentMethodSignatureForInfiniteAttempts.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v7\ChangeAttemptsParameterConsoleOutputRector\ChangeAttemptsParameterConsoleOutputRectorTest
 */
final class ChangeAttemptsParameterConsoleOutputRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ASK_AND_VALIDATE = 'askAndValidate';
    /**
     * @var string
     */
    private const SELECT = 'select';
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Cli\\ConsoleOutput'))) {
            return null;
        }
        if (!$this->isName($node->name, self::SELECT) && !$this->isName($node->name, self::ASK_AND_VALIDATE)) {
            return null;
        }
        if ($this->isName($node->name, self::SELECT) && \count($node->args) < 5) {
            return null;
        }
        if ($this->isName($node->name, self::ASK_AND_VALIDATE) && \count($node->args) < 3) {
            return null;
        }
        $arguments = $node->args;
        // Change the argument for attempts if it false to null
        $nodeNameArgument2 = isset($arguments[2]) ? $this->getName($arguments[2]->value) : '';
        $nodeNameArgument4 = isset($arguments[4]) ? $this->getName($arguments[4]->value) : '';
        if ($this->isName($node->name, self::ASK_AND_VALIDATE) && 'false' === $nodeNameArgument2) {
            $arguments[2] = null;
        } elseif ($this->isName($node->name, self::SELECT) && 'false' === $nodeNameArgument4) {
            $arguments[4] = null;
        }
        $node->args = $this->nodeFactory->createArgs($arguments);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns old default value to parameter in ConsoleOutput->askAndValidate() and/or ConsoleOutput->select() method', [new CodeSample('$this->output->select(\'The question\', [1, 2, 3], null, false, false);', '$this->output->select(\'The question\', [1, 2, 3], null, false, null);')]);
    }
}
