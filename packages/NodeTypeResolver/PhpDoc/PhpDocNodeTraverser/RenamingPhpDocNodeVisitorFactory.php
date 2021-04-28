<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PhpDoc\PhpDocNodeTraverser;

use Rector\NodeTypeResolver\PhpDocNodeVisitor\ClassRenamePhpDocNodeVisitor;
use Symplify\SimplePhpDocParser\PhpDocNodeTraverser;

final class RenamingPhpDocNodeVisitorFactory
{
    /**
     * @var ClassRenamePhpDocNodeVisitor
     */
    private $classRenamePhpDocNodeVisitor;

    public function __construct(ClassRenamePhpDocNodeVisitor $classRenamePhpDocNodeVisitor)
    {
        $this->classRenamePhpDocNodeVisitor = $classRenamePhpDocNodeVisitor;
    }

    public function create(): PhpDocNodeTraverser
    {
        $phpDocNodeTraverser = new PhpDocNodeTraverser();
        $phpDocNodeTraverser->addPhpDocNodeVisitor($this->classRenamePhpDocNodeVisitor);

        return $phpDocNodeTraverser;
    }
}
