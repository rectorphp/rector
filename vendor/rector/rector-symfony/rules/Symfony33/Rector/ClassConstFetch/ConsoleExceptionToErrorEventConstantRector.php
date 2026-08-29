<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony33\Rector\ClassConstFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use RectorPrefix202608\Symfony\Component\Console\ConsoleEvents;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Covers:
 * - https://github.com/symfony/symfony/pull/22441/files
 * - https://github.com/symfony/symfony/blob/master/UPGRADE-3.3.md#console
 *
 * @see \Rector\Symfony\Tests\Symfony33\Rector\ClassConstFetch\ConsoleExceptionToErrorEventConstantRector\ConsoleExceptionToErrorEventConstantRectorTest
 */
final class ConsoleExceptionToErrorEventConstantRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private ObjectType $consoleEventsObjectType;
    public function __construct()
    {
        $this->consoleEventsObjectType = new ObjectType('Symfony\Component\Console\ConsoleEvents');
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/console', '>=3.3');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns old event name with EXCEPTION to ERROR constant in Console in Symfony', [new CodeSample('"console.exception"', ConsoleEvents::class . '::ERROR'), new CodeSample(ConsoleEvents::class . '::EXCEPTION', ConsoleEvents::class . '::ERROR')]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassConstFetch::class, String_::class];
    }
    /**
     * @param ClassConstFetch|String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof String_) {
            if ($node->value !== 'console.exception') {
                return null;
            }
            return $this->nodeFactory->createClassConstFetch($this->consoleEventsObjectType->getClassName(), 'ERROR');
        }
        if ($this->isObjectType($node->class, $this->consoleEventsObjectType) && $this->isName($node->name, 'EXCEPTION')) {
            return $this->nodeFactory->createClassConstFetch($this->consoleEventsObjectType->getClassName(), 'ERROR');
        }
        return null;
    }
}
