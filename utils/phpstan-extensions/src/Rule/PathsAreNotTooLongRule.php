<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Node\FileNode;
use PHPStan\Rules\Rule;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\PathsAreNotTooLongRule\PathsAreNotTooLongRuleTest
 * @implements Rule<FileNode>
 */
final class PathsAreNotTooLongRule implements Rule, ConfigurableRuleInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'The file name %d is too long for Windows. Shorten it under %d.';

    /**
     * @var int
     */
    private $maxFileLength;

    /**
     * In windows the max-path length is 260 chars. we give a bit room for the path up to the rector project.
     */
    public function __construct(int $maxFileLength = 200)
    {
        $this->maxFileLength = $maxFileLength;
    }

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
        if (! $fileName) {
            return [];
        }

        $fileNameLength = strlen($fileName);
        if ($fileNameLength < $this->maxFileLength) {
            return [];
        }

        $errorMessage = sprintf(self::ERROR_MESSAGE, $fileNameLength, $this->maxFileLength);
        return [$errorMessage];
    }
}
