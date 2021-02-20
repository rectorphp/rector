<?php

declare(strict_types=1);

namespace Rector\DependencyInjection\NodeAnalyzer;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Nette\NetteInjectTagNode;
use Rector\FamilyTree\NodeAnalyzer\ClassChildAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class NetteInjectPropertyAnalyzer
{
    /**
     * @var ClassChildAnalyzer
     */
    private $classChildAnalyzer;

    public function __construct(ClassChildAnalyzer $classChildAnalyzer)
    {
        $this->classChildAnalyzer = $classChildAnalyzer;
    }

    public function detect(Property $property, PhpDocInfo $phpDocInfo): bool
    {
        if (! $phpDocInfo->hasByType(NetteInjectTagNode::class)) {
            return false;
        }

        $classLike = $property->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        if ($classLike->isAbstract()) {
            return false;
        }

        if ($this->classChildAnalyzer->hasChildClassConstructor($classLike)) {
            return false;
        }

        if ($this->classChildAnalyzer->hasParentClassConstructor($classLike)) {
            return false;
        }

        // it needs @var tag as well, to get the type
        if ($phpDocInfo->getVarTagValueNode() !== null) {
            return true;
        }

        return $property->type !== null;
    }
}
