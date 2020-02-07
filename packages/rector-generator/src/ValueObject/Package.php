<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\ValueObject;

final class Package
{
    /**
     * @var string
     */
    public const UTILS = 'Utils';

    /**
     * @var string
     */
    private $srcNamespace;

    /**
     * @var string
     */
    private $testsNamespace;

    /**
     * @var string
     */
    private $srcDirectory;

    /**
     * @var string
     */
    private $testsDirectory;

    public function __construct(
        string $srcNamespace,
        string $testsNamespace,
        string $srcDirectory,
        string $testsDirectory
    ) {
        $this->srcNamespace = $srcNamespace;
        $this->testsNamespace = $testsNamespace;
        $this->srcDirectory = $srcDirectory;
        $this->testsDirectory = $testsDirectory;
    }

    public function getSrcNamespace(): string
    {
        return $this->srcNamespace;
    }

    public function getTestsNamespace(): string
    {
        return $this->testsNamespace;
    }

    public function getSrcDirectory(): string
    {
        return $this->srcDirectory;
    }

    public function getTestsDirectory(): string
    {
        return $this->testsDirectory;
    }
}
