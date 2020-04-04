<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\Dir;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;

final class ReplaceDirWithRealPathRector extends AbstractRector implements ClassSyncerRectorInterface
{
    /**
     * @var string
     */
    private const JUST_ADDED_OPTION = 'just_added';

    /**
     * @return class-string[]
     */
    public function getNodeTypes(): array
    {
        return [Dir::class];
    }

    /**
     * @param Dir $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $relativePath = new String_('/../../../vendor/doctrine/annotations/lib/Doctrine/Common/Annotations');

        $concat = new Concat($node, $relativePath);
        $node->setAttribute(self::JUST_ADDED_OPTION, true);

        return $concat;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Replace __DIR__ with relative path');
    }

    private function shouldSkip(Node $node): bool
    {
        if ((bool) $node->getAttribute(self::JUST_ADDED_OPTION)) {
            return true;
        }

        if ($this->isInClassNamed($node, DocParser::class)) {
            return false;
        }

        return ! $this->isInClassNamed($node, AnnotationReader::class);
    }
}
