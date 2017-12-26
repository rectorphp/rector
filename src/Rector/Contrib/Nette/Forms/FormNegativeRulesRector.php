<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Forms;

use PhpParser\Node;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use Rector\NodeAnalyzer\ClassConstAnalyzer;
use Rector\NodeChanger\ClassConstFetchNameChanger;
use Rector\Rector\AbstractRector;

/**
 * Covers https://forum.nette.org/cs/26250-pojdte-otestovat-nette-2-4-rc
 *
 * Before:
 * - ~Form::FILLED
 *
 * After:
 * - Form::NOT_FILLED
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
     * @var ClassConstAnalyzer
     */
    private $classConstAnalyzer;

    /**
     * @var ClassConstFetchNameChanger
     */
    private $classConstFetchNameChanger;

    public function __construct(ClassConstAnalyzer $classConstAnalyzer, ClassConstFetchNameChanger $classConstFetchNameChanger)
    {
        $this->classConstAnalyzer = $classConstAnalyzer;
        $this->classConstFetchNameChanger = $classConstFetchNameChanger;
    }

    /**
     * Detects "~Form::FILLED"
     */
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof BitwiseNot) {
            return false;
        }

        return $this->classConstAnalyzer->isTypeAndNames(
            $node->expr,
            self::FORM_CLASS,
            self::RULE_NAMES
        );
    }

    /**
     * @param BitwiseNot $bitwiseNotNode
     */
    public function refactor(Node $bitwiseNotNode): ?Node
    {
        /** @var ClassConstFetch $classConstFetchNode */
        $classConstFetchNode = $bitwiseNotNode->expr;

        /** @var Identifier $identifierNode */
        $identifierNode = $classConstFetchNode->name;

        $this->classConstFetchNameChanger->renameNode($classConstFetchNode, 'NOT_' . $identifierNode->toString());

        return $classConstFetchNode;
    }
}
