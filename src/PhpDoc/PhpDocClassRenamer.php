<?php declare(strict_types=1);

namespace Rector\PhpDoc;

use Nette\Utils\Strings;
use PhpParser\Comment\Doc;
use PhpParser\Node;

final class PhpDocClassRenamer
{
    /**
     * Covers annotations like @ORM, @Serializer, @Assert etc
     * See https://github.com/rectorphp/rector/issues/1872
     *
     * @param string[] $oldToNewClasses
     */
    public function changeTypeInAnnotationTypes(Node $node, array $oldToNewClasses): void
    {
        $docComment = $node->getDocComment();
        if ($docComment === null) {
            return;
        }

        $textDocComment = $docComment->getText();

        $oldTypes = array_keys($oldToNewClasses);

        $oldTypesPregQuoted = [];
        foreach ($oldTypes as $oldType) {
            $oldTypesPregQuoted[] = '\b' . preg_quote($oldType) . '\b';
        }

        $oldTypesPattern = '#(?|' . implode('|', $oldTypesPregQuoted) . ')#x';

        $match = Strings::match($textDocComment, $oldTypesPattern);
        if ($match === null) {
            return;
        }

        foreach ($match as $matchedOldType) {
            $newType = $oldToNewClasses[$matchedOldType];
            $textDocComment = Strings::replace($textDocComment, '#\b' . preg_quote($matchedOldType) . '\b#', $newType);
        }

        $node->setDocComment(new Doc($textDocComment));
    }
}
