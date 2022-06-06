<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\NodeFactory;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\PostRector\Collector\UseNodesToAddCollector;
use Rector\Restoration\ValueObject\CompleteImportForPartialAnnotation;
use Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType;
final class ImportExtbaseAnnotationIfMissingFactory
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @readonly
     * @var \Rector\PostRector\Collector\UseNodesToAddCollector
     */
    private $useNodesToAddCollector;
    /**
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\PostRector\Collector\UseNodesToAddCollector $useNodesToAddCollector, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->useNodesToAddCollector = $useNodesToAddCollector;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    public function addExtbaseAliasAnnotationIfMissing(\PhpParser\Node $node) : void
    {
        $namespace = $this->betterNodeFinder->findParentType($node, \PhpParser\Node\Stmt\Namespace_::class);
        $completeImportForPartialAnnotation = new \Rector\Restoration\ValueObject\CompleteImportForPartialAnnotation('TYPO3\\CMS\\Extbase\\Annotation', 'Extbase');
        if ($namespace instanceof \PhpParser\Node\Stmt\Namespace_ && $this->isImportMissing($namespace, $completeImportForPartialAnnotation)) {
            $this->useNodesToAddCollector->addUseImport(new \Rector\StaticTypeMapper\ValueObject\Type\AliasedObjectType('Extbase', 'TYPO3\\CMS\\Extbase\\Annotation'));
        }
    }
    private function isImportMissing(\PhpParser\Node\Stmt\Namespace_ $namespace, \Rector\Restoration\ValueObject\CompleteImportForPartialAnnotation $completeImportForPartialAnnotation) : bool
    {
        foreach ($namespace->stmts as $stmt) {
            if (!$stmt instanceof \PhpParser\Node\Stmt\Use_) {
                continue;
            }
            $useUse = $stmt->uses[0];
            // already there
            if (!$this->nodeNameResolver->isName($useUse->name, $completeImportForPartialAnnotation->getUse())) {
                continue;
            }
            if ((string) $useUse->alias !== $completeImportForPartialAnnotation->getAlias()) {
                continue;
            }
            return \false;
        }
        return \true;
    }
}
