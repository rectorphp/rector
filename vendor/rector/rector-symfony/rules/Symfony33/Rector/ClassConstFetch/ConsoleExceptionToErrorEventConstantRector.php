<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony33\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Covers:
 * - https://github.com/symfony/symfony/pull/22441/files
 * - https://github.com/symfony/symfony/blob/master/UPGRADE-3.3.md#console
 *
 * @see \Rector\Symfony\Tests\Symfony33\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector\ConsoleExceptionToErrorEventConstantRectorTest
 */
final class ConsoleExceptionToErrorEventConstantRector extends AbstractRector
{
    /**
     * @readonly
     * @var \PHPStan\Type\ObjectType
     */
    private $consoleEventsObjectType;
    public function __construct()
    {
        $this->consoleEventsObjectType = new ObjectType('Symfony\\Component\\Console\\ConsoleEvents');
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns old event name with EXCEPTION to ERROR constant in Console in Symfony', [new CodeSample('"console.exception"', 'Symfony\\Component\\Console\\ConsoleEvents::ERROR'), new CodeSample('Symfony\\Component\\Console\\ConsoleEvents::EXCEPTION', 'Symfony\\Component\\Console\\ConsoleEvents::ERROR')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassConstFetch::class, String_::class];
    }
    /**
     * @param ClassConstFetch|String_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof ClassConstFetch && ($this->isObjectType($node->class, $this->consoleEventsObjectType) && $this->isName($node->name, 'EXCEPTION'))) {
            return $this->nodeFactory->createClassConstFetch($this->consoleEventsObjectType->getClassName(), 'ERROR');
        }
        if (!$node instanceof String_) {
            return null;
        }
        if ($node->value !== 'console.exception') {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch($this->consoleEventsObjectType->getClassName(), 'ERROR');
    }
}
