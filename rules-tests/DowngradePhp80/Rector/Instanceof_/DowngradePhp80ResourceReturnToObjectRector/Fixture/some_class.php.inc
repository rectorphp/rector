<?php

namespace Rector\Tests\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector\Fixture;

class SomeClass
{
    public function run($obj)
    {
        $obj instanceof \CurlHandle;
        $obj instanceof \CurlMultiHandle;
        $obj instanceof \CurlShareHandle;
        $obj instanceof \Socket;
        $obj instanceof \GdImage;
        $obj instanceof \XMLWriter;
        $obj instanceof \XMLParser;
        $obj instanceof \EnchantBroker;
        $obj instanceof \EnchantDictionary;
        $obj instanceof \OpenSSLCertificate;
        $obj instanceof \OpenSSLCertificateSigningRequest;
        $obj instanceof \Shmop;
        $obj instanceof \SysvMessageQueue;
        $obj instanceof \SysvSemaphore;
        $obj instanceof \SysvSharedMemory;
        $obj instanceof \InflateContext;
        $obj instanceof \DeflateContext;
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Instanceof_\DowngradePhp80ResourceReturnToObjectRector\Fixture;

class SomeClass
{
    public function run($obj)
    {
        is_resource($obj) || $obj instanceof \CurlHandle;
        is_resource($obj) || $obj instanceof \CurlMultiHandle;
        is_resource($obj) || $obj instanceof \CurlShareHandle;
        is_resource($obj) || $obj instanceof \Socket;
        is_resource($obj) || $obj instanceof \GdImage;
        is_resource($obj) || $obj instanceof \XMLWriter;
        is_resource($obj) || $obj instanceof \XMLParser;
        is_resource($obj) || $obj instanceof \EnchantBroker;
        is_resource($obj) || $obj instanceof \EnchantDictionary;
        is_resource($obj) || $obj instanceof \OpenSSLCertificate;
        is_resource($obj) || $obj instanceof \OpenSSLCertificateSigningRequest;
        is_resource($obj) || $obj instanceof \Shmop;
        is_resource($obj) || $obj instanceof \SysvMessageQueue;
        is_resource($obj) || $obj instanceof \SysvSemaphore;
        is_resource($obj) || $obj instanceof \SysvSharedMemory;
        is_resource($obj) || $obj instanceof \InflateContext;
        is_resource($obj) || $obj instanceof \DeflateContext;
    }
}

?>
