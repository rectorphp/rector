<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\FileWithoutNamespace;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\ValueObject\PseudoNamespaceToNamespace;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @api deprecated and soon to be removed
 */
final class PseudoNamespaceToNamespaceRector extends AbstractRector implements ConfigurableRectorInterface
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replaces defined Pseudo_Namespaces by Namespace\\Ones.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
/** @var Some_Chicken $someService */
$someService = new Some_Chicken;
$someClassToKeep = new Some_Class_To_Keep;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
/** @var Some\Chicken $someService */
$someService = new Some\Chicken;
$someClassToKeep = new Some_Class_To_Keep;
CODE_SAMPLE
, [new PseudoNamespaceToNamespace('Some_', ['Some_Class_To_Keep'])])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        // property, method
        return [FileWithoutNamespace::class, Namespace_::class];
    }
    /**
     * @param Namespace_|FileWithoutNamespace $node
     */
    public function refactor(Node $node) : ?Node
    {
        $errorMessage = \sprintf('Rule "%s" is deprecated, as unreliable and too dynamic. Use more robuts RenameClassRector instead.', self::class);
        \trigger_error($errorMessage, \E_USER_WARNING);
        \sleep(3);
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        // for BC
    }
}
