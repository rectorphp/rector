<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Stmt\ClassLike;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Rector\PHPStanExtensions\NodeAnalyzer\SymfonyConfigRectorValueObjectResolver;
use Rector\PHPStanExtensions\NodeAnalyzer\TypeAndNameAnalyzer;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\PathsAreNotTooLongRule\PathsAreNotTooLongRuleTest
 */
final class PathsAreNotTooLongRule implements Rule
{
    private const MAX_LENGTH = 175;
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'The filename "%s" is too long, to be checked out on windows.';

    public function getNodeType(): string
    {
        return Classlike::class;
    }

    /**
     * @param Classlike $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $fileName = $this->getRealPath($node);

        if (!$fileName) {
            return [];
        }

        if (strlen($fileName) < self::MAX_LENGTH) {
            return [];
        }


        $errorMessage = sprintf(self::ERROR_MESSAGE, $fileName);
        return [$errorMessage];
    }

    /**
     * @return false|string
     */
    private function getRealPath(Node $node)
    {
        $fileInfo = $node->getAttribute(SmartFileInfo::class);
        if (! $fileInfo instanceof SmartFileInfo) {
            return false;
        }

        return $fileInfo->getRealPath();
    }
}
