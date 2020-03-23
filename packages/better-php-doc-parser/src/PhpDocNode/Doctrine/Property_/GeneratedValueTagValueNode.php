<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Nette\Utils\Strings;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class GeneratedValueTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    private $strategy;

    /**
     * @var bool
     */
    private $isEmpty = false;

    /**
     * @var bool
     */
    private $hasBrackets = true;

    /**
     * @var bool
     */
    private $isStrategyExplicit = true;

    public function __construct(string $strategy, ?string $annotationContent = null)
    {
        $this->strategy = $strategy;

        if ($annotationContent) {
            $this->isStrategyExplicit = (bool) Strings::contains($annotationContent, 'strategy=');

            $this->isEmpty = $this->isEmpty($annotationContent);
            $this->hasBrackets = $annotationContent === '()';
        }
    }

    public function __toString(): string
    {
        if ($this->isEmpty) {
            return $this->hasBrackets ? '()' : '';
        }

        if (! $this->isStrategyExplicit) {
            return $this->strategy;
        }

        return sprintf('(strategy="%s")', $this->strategy);
    }

    public function getShortName(): string
    {
        return '@ORM\GeneratedValue';
    }

    private function isEmpty(string $annotationContent): bool
    {
        if ($annotationContent === '') {
            return true;
        }

        return $annotationContent === '()';
    }
}
