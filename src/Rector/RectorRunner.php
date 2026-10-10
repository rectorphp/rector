<?php

declare (strict_types=1);
namespace Rector\Rector;

use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitor\CloningVisitor;
use PHPStan\Analyser\MutatingScope;
use Rector\Application\ChangedNodeScopeRefresher;
use Rector\Application\Provider\CurrentFileProvider;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\Contract\Rector\HTMLAverseRectorInterface;
use Rector\Contract\Rector\RectorInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeDecorator\CreatedByRuleDecorator;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\NodeVisitor\PhpDocInfoRemovingNodeVisitor;
use Rector\Skipper\Skipper\Skipper;
use Rector\Skipper\ValueObject\SkipMatch;
use Rector\ValueObject\Application\File;
/**
 * Runs a single Rector rule against a node, with the skip/decorate/scope-refresh bookkeeping
 * that used to live in AbstractRector::enterNode(). Keeps rules decoupled from PhpParser's NodeVisitor.
 *
 * @see \Rector\Tests\Rector\RectorRunner\RectorRunnerTest
 */
final class RectorRunner
{
    /**
     * @readonly
     */
    private Skipper $skipper;
    /**
     * @readonly
     */
    private CreatedByRuleDecorator $createdByRuleDecorator;
    /**
     * @readonly
     */
    private ChangedNodeScopeRefresher $changedNodeScopeRefresher;
    /**
     * @readonly
     */
    private CurrentFileProvider $currentFileProvider;
    /**
     * @var string
     */
    private const EMPTY_NODE_ARRAY_MESSAGE = <<<CODE_SAMPLE
Array of nodes cannot be empty. Ensure "%s->refactor()" returns non-empty array for Nodes.

A) Direct return null for no change:

    return null;

B) Remove the Node:

    return \\PhpParser\\NodeVisitor::REMOVE_NODE;
CODE_SAMPLE;
    public function __construct(Skipper $skipper, CreatedByRuleDecorator $createdByRuleDecorator, ChangedNodeScopeRefresher $changedNodeScopeRefresher, CurrentFileProvider $currentFileProvider)
    {
        $this->skipper = $skipper;
        $this->createdByRuleDecorator = $createdByRuleDecorator;
        $this->changedNodeScopeRefresher = $changedNodeScopeRefresher;
        $this->currentFileProvider = $currentFileProvider;
    }
    /**
     * @return NodeVisitor::REMOVE_NODE|Node|Node[]|null
     */
    public function run(RectorInterface $rector, Node $node)
    {
        $file = $this->getFile();
        if ($rector instanceof HTMLAverseRectorInterface && $file->containsHTML()) {
            return null;
        }
        $filePath = $file->getFilePath();
        // node already changed by this rule in a previous pass → hard skip
        if ($this->skipper->shouldSkipCurrentNode(get_class($rector), $node)) {
            return null;
        }
        // class/path skip is configured for this rule and file: run the rule on a deep clone to learn
        // whether it would actually have changed anything. Only a skip that prevents a real change
        // counts as used; the original node is left untouched, so the file stays skipped either way.
        $skipMatch = $this->skipper->matchSkip($rector, $filePath);
        if ($skipMatch instanceof SkipMatch) {
            if ($rector->refactor($this->cloneNode($node)) !== null) {
                $this->skipper->markSkipUsed($skipMatch);
            }
            return null;
        }
        // ensure origNode pulled before refactor to avoid changed during refactor, ref https://3v4l.org/YMEGN
        $originalNode = $node->getAttribute(AttributeKey::ORIGINAL_NODE) ?? $node;
        $refactoredNodeOrState = $rector->refactor($node);
        // nothing to change → continue
        if ($refactoredNodeOrState === null) {
            return null;
        }
        if ($refactoredNodeOrState === []) {
            $errorMessage = sprintf(self::EMPTY_NODE_ARRAY_MESSAGE, get_class($rector));
            throw new ShouldNotHappenException($errorMessage);
        }
        if (is_int($refactoredNodeOrState)) {
            $this->createdByRuleDecorator->decorate($node, $originalNode, get_class($rector));
            // only remove node is supported
            if ($refactoredNodeOrState !== NodeVisitor::REMOVE_NODE) {
                throw new ShouldNotHappenException(sprintf('Unsupported state "%d" returned from "%s".', $refactoredNodeOrState, get_class($rector)));
            }
            // notify this rule changed code
            $rectorWithLineChange = new RectorWithLineChange(get_class($rector), $originalNode->getStartLine());
            $file->addRectorClassWithLine($rectorWithLineChange);
            return $refactoredNodeOrState;
        }
        return $this->postRefactorProcess($rector, $file, $originalNode, $node, $refactoredNodeOrState, $filePath);
    }
    private function getFile(): File
    {
        $file = $this->currentFileProvider->getFile();
        if (!$file instanceof File) {
            throw new ShouldNotHappenException('File object is missing. Make sure you call $this->currentFileProvider->setFile(...) before traversing.');
        }
        return $file;
    }
    /**
     * Deep clone, so a skipped rule can be probed on the clone without mutating the real node.
     */
    private function cloneNode(Node $node): Node
    {
        $nodeTraverser = new NodeTraverser(new CloningVisitor(), new PhpDocInfoRemovingNodeVisitor());
        return $nodeTraverser->traverse([$node])[0];
    }
    /**
     * @param Node|Node[] $refactoredNode
     * @return Node|Node[]
     */
    private function postRefactorProcess(RectorInterface $rector, File $file, Node $originalNode, Node $node, $refactoredNode, string $filePath)
    {
        /** @var non-empty-array<Node>|Node $refactoredNode */
        $this->createdByRuleDecorator->decorate($refactoredNode, $originalNode, get_class($rector));
        $rectorWithLineChange = new RectorWithLineChange(get_class($rector), $originalNode->getStartLine());
        $file->addRectorClassWithLine($rectorWithLineChange);
        /** @var MutatingScope|null $currentScope */
        $currentScope = $node->getAttribute(AttributeKey::SCOPE);
        $this->refreshScopeNodes($refactoredNode, $filePath, $currentScope);
        return $refactoredNode;
    }
    /**
     * @param Node[]|Node $node
     */
    private function refreshScopeNodes($node, string $filePath, ?MutatingScope $mutatingScope): void
    {
        $nodes = $node instanceof Node ? [$node] : $node;
        foreach ($nodes as $node) {
            $this->changedNodeScopeRefresher->refresh($node, $filePath, $mutatingScope);
        }
    }
}
