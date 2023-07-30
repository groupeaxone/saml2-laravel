<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Test\SAML2\XML\saml;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SimpleSAML\SAML2\XML\saml\AuthenticatingAuthorityTest
 *
 * @covers \SimpleSAML\SAML2\XML\saml\AuthenticatingAuthority
 * @covers \SimpleSAML\SAML2\XML\saml\AbstractSamlElement
 *
 * @package simplesamlphp/saml2
 */
final class AuthenticatingAuthorityTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-assertion-2.0.xsd';

        self::$testedClass = AuthenticatingAuthority::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/saml_AuthenticatingAuthority.xml',
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $authenticatingAuthority = new AuthenticatingAuthority('https://idp.example.com/SAML2');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authenticatingAuthority),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $authenticatingAuthority = AuthenticatingAuthority::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($authenticatingAuthority),
        );
    }
}
