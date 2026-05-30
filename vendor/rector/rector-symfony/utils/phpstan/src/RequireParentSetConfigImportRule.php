<?php

declare (strict_types=1);
namespace Rector\Symfony\Utils\PHPStan;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
/**
 * Every nested set config (e.g. "symfony81/symfony81-filesystem.php") must be
 * imported in its parent set config (e.g. "symfony81.php"), otherwise its rules
 * are silently never loaded.
 *
 * @see \Rector\Symfony\Utils\PHPStan\Tests\RequireParentSetConfigImportRuleTest
 *
 * @implements Rule<Return_>
 */
final class RequireParentSetConfigImportRule implements Rule
{
    /**
     * @var string
     */
    private const ERROR_MESSAGE = 'Config file "%s" must be imported in parent set config "%s"';
    public function getNodeType(): string
    {
        return Return_::class;
    }
    /**
     * @return list<IdentifierRuleError>
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $parentSetFilePath = $scope->getFile();
        // only set configs are relevant here
        if (strpos($parentSetFilePath, '/config/sets/') === \false) {
            return [];
        }
        // is this a parent set config? it must have a sibling directory of the same name
        $nestedSetDirectory = (string) substr($parentSetFilePath, 0, -strlen('.php'));
        if (!is_dir($nestedSetDirectory)) {
            return [];
        }
        $nestedSetFilePaths = glob($nestedSetDirectory . '/*.php');
        if ($nestedSetFilePaths === \false || $nestedSetFilePaths === []) {
            return [];
        }
        $importedFileNames = $this->resolveImportedFileNames($node);
        $ruleErrors = [];
        foreach ($nestedSetFilePaths as $nestedSetFilePath) {
            $nestedSetFileName = basename($nestedSetFilePath);
            if (in_array($nestedSetFileName, $importedFileNames, \true)) {
                continue;
            }
            $ruleErrors[] = RuleErrorBuilder::message(sprintf(self::ERROR_MESSAGE, $nestedSetFileName, basename($parentSetFilePath)))->identifier('rector.parentSetConfigImport')->build();
        }
        return $ruleErrors;
    }
    /**
     * @return string[]
     */
    private function resolveImportedFileNames(Return_ $return): array
    {
        $nodeFinder = new NodeFinder();
        $importedFileNames = [];
        /** @var MethodCall[] $methodCalls */
        $methodCalls = $nodeFinder->findInstanceOf($return, MethodCall::class);
        foreach ($methodCalls as $methodCall) {
            if (!$methodCall->name instanceof Identifier) {
                continue;
            }
            if ($methodCall->name->toString() !== 'import') {
                continue;
            }
            /** @var String_[] $strings */
            $strings = $nodeFinder->findInstanceOf($methodCall->getArgs(), String_::class);
            foreach ($strings as $string) {
                if (substr_compare($string->value, '.php', -strlen('.php')) !== 0) {
                    continue;
                }
                $importedFileNames[] = basename($string->value);
            }
        }
        return $importedFileNames;
    }
}
