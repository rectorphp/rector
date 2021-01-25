<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class Response
{
}

final class RedirectResponse extends Response
{
}

final class SkipParentAndChild
{
    public function run(bool $response): Response
    {
        if ($response) {
            return new RedirectResponse;
        }

        return new Response;
    }
}
