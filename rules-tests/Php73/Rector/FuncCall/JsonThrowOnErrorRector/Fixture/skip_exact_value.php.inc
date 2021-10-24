<?php

namespace Rector\Tests\Php73\Rector\FuncCall\JsonThrowOnErrorRector\Fixture;

function skipExactValue(){
    $jsonData = [
        'validation_messages' =>
        [
            'foo' => [
                'regexNotMatch' => 'The input does not match against pattern \'/^[a-zA-Z0-9 .\-]+$/\''
            ]
        ],
        'type' => 'http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html',
        'title' => 'Unprocessable Entity',
        'status' => 422,
        'detail' => 'Failed Validation'
    ];

    $response = json_encode($jsonData);
    assert(is_string($response));
}

function skipExactValue2(){
    $json = '{}';

    $response = json_decode($json);
    assert(is_array($response));
}
