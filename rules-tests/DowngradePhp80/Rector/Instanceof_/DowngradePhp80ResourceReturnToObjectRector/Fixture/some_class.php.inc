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
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
        is_resource($obj);
    }
}

?>
