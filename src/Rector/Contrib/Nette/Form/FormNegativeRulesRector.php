<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Form;

use PhpParser\Node;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;

/**
 * Covers https://forum.nette.org/cs/26250-pojdte-otestovat-nette-2-4-rc
 */
final class FormNegativeRulesRector extends AbstractRector
{
    /**
     * @var string
     */
    public const FORM_CLASS = 'Nette\Application\UI\Form';

    /**
     * @var string[]
     */
    private const RULE_NAMES = ['FILLED', 'EQUAL'];

    /**
     * Detects "~Form::FILLED"
     */
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof BitwiseNot) {
            return false;
        }

        return $this->isClassConstFetchOfClassNameAndConstantNames($node->expr, self::FORM_CLASS, self::RULE_NAMES);
    }

    /**
     * @param string[] $constantNames
     */
    public function isClassConstFetchOfClassNameAndConstantNames(Node $node, string $className, array $constantNames): bool
    {
        if (! $node instanceof ClassConstFetch) {
            return false;
        }

        $classConstFetchNode = $node;

        /** @var FullyQualified $className */
        $classFullyQualifiedName = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME);
        $nodeClassName = $classFullyQualifiedName->toString();
        if ($nodeClassName !== $className) {
            return false;
        }

        $nodeConstantName = $classConstFetchNode->name->name;

        return in_array($nodeConstantName, $constantNames, true);
    }

    /**
     * @param BitwiseNot $bitwiseNotNode
     */
    public function refactor(Node $bitwiseNotNode): ?Node
    {
        /** @var ClassConstFetch $classConstFetchNode */
        $classConstFetchNode = $bitwiseNotNode->expr;

        $oldRuleName = $classConstFetchNode->name->name;

        $classConstFetchNode->name->name = 'NOT_' . $oldRuleName;

        return $classConstFetchNode;
    }
}
