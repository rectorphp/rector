<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony40\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony40\Rector\MethodCall\ProcessBuilderGetProcessRector\ProcessBuilderGetProcessRectorTest
 */
final class ProcessBuilderGetProcessRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Removes `$processBuilder->getProcess()` calls to $processBuilder in Process in Symfony, because ProcessBuilder was removed. This is part of multi-step Rector and has very narrow focus.', [new CodeSample(<<<'CODE_SAMPLE'
$processBuilder = new Symfony\Component\Process\ProcessBuilder;
$process = $processBuilder->getProcess();
$commamdLine = $processBuilder->getProcess()->getCommandLine();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$processBuilder = new Symfony\Component\Process\ProcessBuilder;
$process = $processBuilder;
$commamdLine = $processBuilder->getCommandLine();
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
        if (!$this->isName($node->name, 'getProcess')) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType('Symfony\\Component\\Process\\ProcessBuilder'))) {
            return null;
        }
        return $node->var;
    }
}
