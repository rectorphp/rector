<?php

declare(strict_types=1);

namespace Rector\PhpAttribute;

use Nette\Utils\Strings;
use PhpParser\Node\Stmt\Use_;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\PhpAttribute\ValueObject\UseAliasMetadata;

/**
 * @see \Rector\Tests\PhpAttribute\UseAliasNameMatcherTest
 */
final class UseAliasNameMatcher
{
    /**
     * @param Use_[] $uses
     */
    public function match(
        array $uses,
        string $shortAnnotationName,
        AnnotationToAttribute $annotationToAttribute
    ): ?UseAliasMetadata {
        $shortAnnotationName = trim($shortAnnotationName, '@');

        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->alias === null) {
                    continue;
                }

                $alias = $useUse->alias->toString();
                if (! str_starts_with($shortAnnotationName, $alias)) {
                    continue;
                }

                $importName = $useUse->name->toString();

                // previous keyword
                $lastImportKeyword = Strings::after($importName, '\\', -1);
                if ($lastImportKeyword === null) {
                    continue;
                }

                // resolve new short name
                $newShortname = Strings::after($annotationToAttribute->getAttributeClass(), $lastImportKeyword);

                $beforeImportName = Strings::before(
                    $annotationToAttribute->getAttributeClass(),
                    $lastImportKeyword
                ) . $lastImportKeyword;

                return new UseAliasMetadata($alias . $newShortname, $beforeImportName, $useUse);
            }
        }

        return null;
    }
}
