<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Utilities;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Utilities\Certificate;
use SimpleSAML\Test\SAML2\CertificatesMock;

use function str_replace;

class CertificateTest extends TestCase
{
    /**
     * @group utilities
     * @test
     * @return void
     */
    public function testValidStructure(): void
    {
        $result = Certificate::hasValidStructure(CertificatesMock::getPlainPublicKey());
        $this->assertTrue($result);
        $result = Certificate::hasValidStructure(CertificatesMock::getPlainInvalidPublicKey());
        $this->assertFalse($result);
    }


    /**
     * @group utilities
     * @test
     * @return void
     */
    public function testConvertToCertificate(): void
    {
        $result = Certificate::convertToCertificate(CertificatesMock::getPlainPublicKeyContents());
        // the formatted public key in CertificatesMock is stored with unix newlines
        $this->assertEquals(CertificatesMock::getPlainPublicKey() . "\n", str_replace("\r", "", $result));
    }
}
