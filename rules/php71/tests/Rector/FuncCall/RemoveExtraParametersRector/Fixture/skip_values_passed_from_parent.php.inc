<?php

namespace Rector\Php71\Tests\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

final class SkipValuePassedFromParent extends HttpException
{
    private $apiError;

    public function __construct(ApiError $apiError, ?\Throwable $previous = null, array $headers = [], $code = 0)
    {
        $this->apiError = $apiError;
        $statusCode = $apiError->getStatusCode();
        $message = $apiError->getMessage();

        parent::__construct($statusCode, $message, $previous, $headers, $code);
    }
}

class HttpException extends \Exception
{
    public function __construct(
        int $statusCode,
        string $message = null,
        \Throwable $previous = null,
        array $headers = [],
        ?int $code = 0
    ) {
    }
}