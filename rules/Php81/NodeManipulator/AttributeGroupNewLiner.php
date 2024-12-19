<?php

declare (strict_types=1);
namespace Rector\Php81\NodeManipulator;

use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use Rector\ValueObject\Application\File;
final class AttributeGroupNewLiner
{
    /**
     * @param \PhpParser\Node\Stmt\Property|\PhpParser\Node\Param|\PhpParser\Node\Stmt\Class_ $node
     */
    public function newLine(File $file, $node) : void
    {
        $oldTokens = $file->getOldTokens();
        $startTokenPos = $node->getStartTokenPos();
        if (!isset($oldTokens[$startTokenPos])) {
            return;
        }
        if ($oldTokens[$startTokenPos]->text !== '#[') {
            return;
        }
        $iteration = 1;
        $lastKey = \array_key_last($node->attrGroups);
        if ($lastKey === null) {
            return;
        }
        $lastAttributeTokenPos = $node->attrGroups[$lastKey]->getEndTokenPos();
        while (isset($oldTokens[$startTokenPos + $iteration])) {
            if ($startTokenPos + $iteration === $lastAttributeTokenPos) {
                if ($oldTokens[$startTokenPos + $iteration]->text !== ']') {
                    break;
                }
                if (\trim($oldTokens[$startTokenPos + $iteration + 1]->text ?? '') === '') {
                    $space = \ltrim($oldTokens[$startTokenPos + $iteration + 1]->text ?? '', "\r\n");
                } elseif (\trim($oldTokens[$startTokenPos - 1]->text ?? '') === '') {
                    $space = \ltrim($oldTokens[$startTokenPos - 1]->text ?? '', "\r\n");
                } else {
                    $space = '';
                }
                $oldTokens[$startTokenPos + $iteration]->text = "]\n" . $space;
                break;
            }
            ++$iteration;
        }
    }
}
