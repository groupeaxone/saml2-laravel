<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Certificate;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Configuration\PrivateKey as ConfPrivateKey;
use SimpleSAML\SAML2\Certificate\PrivateKey;
use SimpleSAML\SAML2\Certificate\PrivateKeyLoader;

class PrivateKeyLoaderTest extends TestCase
{
    /** @var \SimpleSAML\SAML2\Certificate\PrivateKeyLoader */
    private static PrivateKeyLoader $privateKeyLoader;


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$privateKeyLoader = new PrivateKeyLoader();
    }


    /**
     * @group        certificate
     * @test
     * @dataProvider privateKeyTestProvider
     *
     * @param \SimpleSAML\SAML2\Configuration\PrivateKey $configuredKey
     * @return void
     */
    public function loading_a_configured_private_key_returns_a_certificate_private_key(
        ConfPrivateKey $configuredKey
    ): void {
        $resultingKey = self::$privateKeyLoader->loadPrivateKey($configuredKey);

        $this->assertInstanceOf(PrivateKey::class, $resultingKey);
        $this->assertEquals($resultingKey->getKeyAsString(), "This would normally contain the private key data.\n");
        $this->assertEquals($resultingKey->getPassphrase(), $configuredKey->getPassPhrase());
    }


    /**
     * Dataprovider for 'loading_a_configured_private_key_returns_a_certificate_private_key'
     *
     * @return array
     */
    public static function privateKeyTestProvider(): array
    {
        return [
            'no passphrase'   => [
                new ConfPrivateKey(
                    dirname(__FILE__) . '/File/a_fake_private_key_file.pem',
                    ConfPrivateKey::NAME_DEFAULT
                )
            ],
            'with passphrase' => [
                new ConfPrivateKey(
                    dirname(__FILE__) . '/File/a_fake_private_key_file.pem',
                    ConfPrivateKey::NAME_DEFAULT,
                    'foo bar baz'
                )
            ],
            'private key as contents' => [
                new ConfPrivateKey(
                    file_get_contents(dirname(__FILE__) . '/File/a_fake_private_key_file.pem'),
                    ConfPrivateKey::NAME_DEFAULT,
                    '',
                    false
                )
            ],
        ];
    }
}
