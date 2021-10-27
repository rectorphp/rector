<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeOpenSSL
{
    public function run()
    {
        $dn = [
            "countryName" => "GB",
            "stateOrProvinceName" => "Somerset",
            "localityName" => "Glastonbury",
            "organizationName" => "The Brain Room Limited",
            "organizationalUnitName" => "PHP Documentation Team",
            "commonName" => "Wez Furlong",
            "emailAddress" => "wez@example.com"
        ];

        // Generate a new private (and public) key pair
        $privkey = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        // Generate a certificate signing request
        $csr = openssl_csr_new($dn, $privkey, array('digest_alg' => 'sha256'));
        is_resource($csr);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeOpenSSL
{
    public function run()
    {
        $dn = [
            "countryName" => "GB",
            "stateOrProvinceName" => "Somerset",
            "localityName" => "Glastonbury",
            "organizationName" => "The Brain Room Limited",
            "organizationalUnitName" => "PHP Documentation Team",
            "commonName" => "Wez Furlong",
            "emailAddress" => "wez@example.com"
        ];

        // Generate a new private (and public) key pair
        $privkey = openssl_pkey_new([
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ]);

        // Generate a certificate signing request
        $csr = openssl_csr_new($dn, $privkey, array('digest_alg' => 'sha256'));
        $csr instanceof \OpenSSLCertificateSigningRequest;
    }
}

?>
