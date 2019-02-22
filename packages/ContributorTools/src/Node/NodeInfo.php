<?php declare(strict_types=1);

namespace Rector\ContributorTools\Node;

final class NodeInfo
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $printedContent;

    /**
     * @var bool
     */
    private $hasRequiredArguments = false;

    public function __construct(string $class, string $printedContent, bool $hasRequiredArguments)
    {
        $this->class = $class;
        $this->printedContent = $printedContent;
        $this->hasRequiredArguments = $hasRequiredArguments;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getPrintedContent(): string
    {
        return $this->printedContent;
    }

    public function hasRequiredArguments(): bool
    {
        return $this->hasRequiredArguments;
    }
}
