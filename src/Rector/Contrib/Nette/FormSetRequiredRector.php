<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use Rector\Deprecation\SetNames;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeFactory\NodeFactory;
use Rector\Rector\AbstractRector;

/**
 * Covers https://forum.nette.org/cs/26672-missing-setrequired-true-false-on-field-abc-in-form
 */
final class FormSetRequiredRector extends AbstractRector
{
    /**
     * @var string
     */
    public const FORM_CLASS = 'Nette\Application\UI\Form';

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var NodeFactory
     */
    private $nodeFactory;

    public function __construct(MethodCallAnalyzer $methodCallAnalyzer, NodeFactory $nodeFactory)
    {
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->nodeFactory = $nodeFactory;
    }

    public function getSetName(): string
    {
        return SetNames::NETTE;
    }

    public function sinceVersion(): float
    {
        return 2.4;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->methodCallAnalyzer->isMethodCallTypeAndMethods($node, self::FORM_CLASS, ['addCondition'])) {
            return false;
        }

        /** @var MethodCall $node */
        if (count($node->args) !== 1) {
            return false;
        }

        $arg = $node->args[0];
        if (! $arg->value instanceof ClassConstFetch) {
            return false;
        }

        if ($arg->value->class->getAttribute('type') !== self::FORM_CLASS) {
            return false;
        }

        return $arg->value->name->name === 'FILLED';
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        $args = [
            new Arg($this->nodeFactory->createFalseConstant()),
        ];

        return new MethodCall($node->var, 'setRequired', $args);
    }
}
