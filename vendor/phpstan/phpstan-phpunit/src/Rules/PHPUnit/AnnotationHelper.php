<?php

declare (strict_types=1);
namespace PHPStan\Rules\PHPUnit;

use PhpParser\Comment\Doc;
use PHPStan\Rules\RuleError;
use PHPStan\Rules\RuleErrorBuilder;
use function array_key_exists;
use function in_array;
use function preg_match;
use function preg_split;
class AnnotationHelper
{
    private const ANNOTATIONS_WITH_PARAMS = ['backupGlobals', 'backupStaticAttributes', 'covers', 'coversDefaultClass', 'dataProvider', 'depends', 'group', 'preserveGlobalState', 'requires', 'testDox', 'testWith', 'ticket', 'uses'];
    /**
     * @return RuleError[] errors
     */
    public function processDocComment(Doc $docComment) : array
    {
        $errors = [];
        $docCommentLines = preg_split("/((\r?\n)|(\r\n?))/", $docComment->getText());
        if ($docCommentLines === \false) {
            return [];
        }
        foreach ($docCommentLines as $docCommentLine) {
            // These annotations can't be retrieved using the getResolvedPhpDoc method on the FileTypeMapper as they are not present when they are invalid
            $annotation = preg_match('/(?<annotation>@(?<property>[a-zA-Z]+)(?<whitespace>\\s*)(?<value>.*))/', $docCommentLine, $matches);
            if ($annotation === \false) {
                continue;
                // Line without annotation
            }
            if (array_key_exists('property', $matches) === \false || array_key_exists('whitespace', $matches) === \false || array_key_exists('annotation', $matches) === \false) {
                continue;
            }
            if (!in_array($matches['property'], self::ANNOTATIONS_WITH_PARAMS, \true) || $matches['whitespace'] !== '') {
                continue;
            }
            $errors[] = RuleErrorBuilder::message('Annotation "' . $matches['annotation'] . '" is invalid, "@' . $matches['property'] . '" should be followed by a space and a value.')->build();
        }
        return $errors;
    }
}
