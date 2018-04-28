<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Forms;

use PhpParser\Node;
use PhpParser\Node\Expr\BitwiseNot;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\ClassConstAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

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
     * @var ClassConstAnalyzer
     */
    private $classConstAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    public function __construct(ClassConstAnalyzer $classConstAnalyzer, IdentifierRenamer $identifierRenamer)
    {
        $this->classConstAnalyzer = $classConstAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns negative Nette Form rules to their specific new names.', [
            new CodeSample('$form->addRule(~Form::FILLED);', '$form->addRule(Form::NOT_FILLED);'),
        ]);
    }

    /**
     * Detects "~Form::FILLED"
     */
    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof BitwiseNot) {
            return false;
        }

        return $this->classConstAnalyzer->isTypeAndNames($node->expr, self::FORM_CLASS, self::RULE_NAMES);
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

        $this->identifierRenamer->renameNode($classConstFetchNode, 'NOT_' . $identifierNode->toString());

        return $classConstFetchNode;
    }
}
