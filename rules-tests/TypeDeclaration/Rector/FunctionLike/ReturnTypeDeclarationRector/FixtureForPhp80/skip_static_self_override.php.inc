<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

final class SkipSelfStaticOverride
{
    private $relativePathname;

    public static function create(): self
    {
        return new self();
    }

    public function withPathname(string $pathname): self
    {
        $this->relativePathname = $pathname;

        return $this;
    }
}
