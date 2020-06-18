<?php

declare(strict_types=1);

namespace Rector\Core\Rector\FuncCall;

use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Comments\CommentableNodeResolver;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeRemoval\BreakingRemovalGuard;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://twitter.com/afilina & Zenika (CAN) for sponsoring this rule - visit them on https://zenika.ca/en/en
 *
 * @see \Rector\Core\Tests\Rector\FuncCall\RemoveIniGetSetFuncCallRector\RemoveIniGetSetFuncCallRectorTest
 */
final class RemoveIniGetSetFuncCallRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $keysToRemove = [];

    /**
     * @var BreakingRemovalGuard
     */
    private $breakingRemovalGuard;

    /**
     * @var CommentableNodeResolver
     */
    private $commentableNodeResolver;

    /**
     * @param string[] $keysToRemove
     */
    public function __construct(
        BreakingRemovalGuard $breakingRemovalGuard,
        CommentableNodeResolver $commentableNodeResolver,
        array $keysToRemove = []
    ) {
        $this->keysToRemove = $keysToRemove;
        $this->breakingRemovalGuard = $breakingRemovalGuard;
        $this->commentableNodeResolver = $commentableNodeResolver;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove ini_get by configuration', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
ini_get('y2k_compliance');
ini_set('y2k_compliance', 1);
CODE_SAMPLE
                ,
                '',
                [
                    '$keysToRemove' => ['y2k_compliance'],
                ]
            ), ]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [FuncCall::class];
    }

    /**
     * @param FuncCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isNames($node, ['ini_get', 'ini_set'])) {
            return null;
        }

        if (! isset($node->args[0])) {
            return null;
        }

        $keyValue = $this->getValue($node->args[0]->value);
        if (! in_array($keyValue, $this->keysToRemove, true)) {
            return null;
        }

        if ($this->breakingRemovalGuard->isLegalNodeRemoval($node)) {
            $this->removeNode($node);
        } else {
            $commentableNode = $this->commentableNodeResolver->resolve($node);
            $commentableNode->setAttribute(AttributeKey::COMMENTS, [new Comment('// @fixme')]);
        }

        return null;
    }
}
