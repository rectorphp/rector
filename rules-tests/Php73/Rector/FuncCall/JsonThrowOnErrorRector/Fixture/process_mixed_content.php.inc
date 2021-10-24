<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function processMixedContent(array $message, int $code, string $detail)
{
    $jsonData = [
        'validation_messages' => $message,
        'type' => 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html',
        'title' => 'Unprocessable Entity',
        'status' => $code,
        'detail' => $detail,
    ];

    $response = json_encode($jsonData);
    assert(is_string($response));
}

function processMixedContent2($data){
    $json = "{$data}";

    $response = json_decode($json);
    assert(is_array($response));
}

?>
-----
<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function processMixedContent(array $message, int $code, string $detail)
{
    $jsonData = [
        'validation_messages' => $message,
        'type' => 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html',
        'title' => 'Unprocessable Entity',
        'status' => $code,
        'detail' => $detail,
    ];

    $response = json_encode($jsonData, JSON_THROW_ON_ERROR);
    assert(is_string($response));
}

function processMixedContent2($data){
    $json = "{$data}";

    $response = json_decode($json, null, 512, JSON_THROW_ON_ERROR);
    assert(is_array($response));
}

?>
