<?php

declare (strict_types=1);
namespace Rector\PhpAttribute;

use RectorPrefix20220531\Nette\Utils\Strings;
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
    public function match(array $uses, string $shortAnnotationName, \Rector\Php80\ValueObject\AnnotationToAttribute $annotationToAttribute) : ?\Rector\PhpAttribute\ValueObject\UseAliasMetadata
    {
        $shortAnnotationName = \trim($shortAnnotationName, '@');
        foreach ($uses as $use) {
            foreach ($use->uses as $useUse) {
                if ($useUse->alias === null) {
                    continue;
                }
                $alias = $useUse->alias->toString();
                if (\strncmp($shortAnnotationName, $alias, \strlen($alias)) !== 0) {
                    continue;
                }
                $importName = $useUse->name->toString();
                // previous keyword
                $lastImportKeyword = \RectorPrefix20220531\Nette\Utils\Strings::after($importName, '\\', -1);
                if ($lastImportKeyword === null) {
                    continue;
                }
                // resolve new short name
                $newShortname = \RectorPrefix20220531\Nette\Utils\Strings::after($annotationToAttribute->getAttributeClass(), $lastImportKeyword);
                $beforeImportName = \RectorPrefix20220531\Nette\Utils\Strings::before($annotationToAttribute->getAttributeClass(), $lastImportKeyword) . $lastImportKeyword;
                return new \Rector\PhpAttribute\ValueObject\UseAliasMetadata($alias . $newShortname, $beforeImportName, $useUse);
            }
        }
        return null;
    }
}
