<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PHPStan\Node\FileNode;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\NodeAnalyzer\SymfonyConfigRectorValueObjectResolver;
use Rector\PHPStanExtensions\NodeAnalyzer\TypeAndNameAnalyzer;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\PathsAreNotTooLongRule\PathsAreNotTooLongRuleTest
 * @implements Rule<FileNode>
 */
final class PathsAreNotTooLongRule implements Rule
{
    private const MAX_LENGTH = 175;
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'The file "%s" is too long, to be checked out on windows (%s chars; limit %s).';

    public function getNodeType(): string
    {
        return FileNode::class;
    }

    /**
     * @param FileNode $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $fileName = $scope->getFile();

        if (!$fileName) {
            return [];
        }

        if (strlen($fileName) < self::MAX_LENGTH) {
            return [];
        }

        $errorMessage = sprintf(self::ERROR_MESSAGE, $fileName, strlen($fileName), self::MAX_LENGTH);
        return [$errorMessage];
    }
}
