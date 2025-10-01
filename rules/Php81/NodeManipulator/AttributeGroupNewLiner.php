<?php

declare (strict_types=1);
namespace Rector\Php81\NodeManipulator;

use PhpParser\Node;
use PhpParser\Node\AttributeGroup;
use Rector\ValueObject\Application\File;
use RectorPrefix202510\Webmozart\Assert\Assert;
final class AttributeGroupNewLiner
{
    public function newLine(File $file, Node $node): void
    {
        $attrGroups = $node->attrGroups ?? [];
        if ($attrGroups === []) {
            return;
        }
        Assert::allIsAOf($attrGroups, AttributeGroup::class);
        Assert::isArray($attrGroups);
        $oldTokens = $file->getOldTokens();
        $startTokenPos = $node->getStartTokenPos();
        if (!isset($oldTokens[$startTokenPos])) {
            return;
        }
        if ($oldTokens[$startTokenPos]->text !== '#[') {
            return;
        }
        $iteration = 1;
        $lastKey = array_key_last($attrGroups);
        if ($lastKey === null) {
            return;
        }
        $lastAttributeTokenPos = $attrGroups[$lastKey]->getEndTokenPos();
        while (isset($oldTokens[$startTokenPos + $iteration])) {
            if ($startTokenPos + $iteration === $lastAttributeTokenPos) {
                if ($oldTokens[$startTokenPos + $iteration]->text !== ']') {
                    break;
                }
                if (trim($oldTokens[$startTokenPos + $iteration + 1]->text ?? '') === '') {
                    $space = ltrim($oldTokens[$startTokenPos + $iteration + 1]->text ?? '', "\r\n");
                } elseif (trim($oldTokens[$startTokenPos - 1]->text ?? '') === '') {
                    $space = ltrim($oldTokens[$startTokenPos - 1]->text ?? '', "\r\n");
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
