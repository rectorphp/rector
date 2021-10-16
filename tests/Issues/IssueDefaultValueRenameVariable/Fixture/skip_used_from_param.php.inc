<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueDefaultValueRenameParam\Fixture;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\Node as DocNode;
use PHPStan\PhpDocParser\Ast\Node as PhpDocNode;
use Rector\BetterPhpDocParser\AnnotationAnalyzer\DoctrineAnnotationTagValueNodeAnalyzer;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;

final class SkipUsedFromParam
{
    public function check(DoctrineAnnotationTagValueNodeAnalyzer $analyzer, PhpDocNode $docNode)
    {
        $doctrineAnnotationTagValueNode = $analyzer->resolveDoctrineAnnotationTagValueNode($docNode);

        if (! $doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
    }
}

?>
