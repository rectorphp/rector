<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

final class KeepVariousRequest
{
    public function collection(): Response
    {
        return new Response();
    }

    public function noContent(): \Symfony\Component\HttpFoundation\Response
    {
        return new \Symfony\Component\HttpFoundation\Response('', 204);
    }

    public function ok(): \Symfony\Component\HttpFoundation\Response
    {
        return new \Symfony\Component\HttpFoundation\Response('', 200);
    }
}
