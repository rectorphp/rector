<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\NodeAnalyzer;

use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Use_;
use Rector\Node\Attribute;

final class NamespaceAnalyzer
{
    /**
     * @return string[]
     */
    public function detectInClass(ClassLike $classLikeNode): array
    {
        $useStatements = [];

        $previousNode = $classLikeNode->getAttribute(Attribute::PREVIOUS_NODE);
        while ($previousNode instanceof Use_) {
            $useStatements[] = $previousNode->uses[0]->name->toString();
            $previousNode = $previousNode->getAttribute(Attribute::PREVIOUS_NODE);
        }

        return $useStatements;
    }
}
