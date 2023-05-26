<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\NameIDMappingService;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Tests for md:NameIDMappingService.
 *
 * @covers \SimpleSAML\SAML2\XML\md\NameIDMappingService
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class NameIDMappingServiceTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = NameIDMappingService::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_NameIDMappingService.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a NameIDMappingService from scratch.
     */
    public function testMarshalling(): void
    {
        $nidmsep = new NameIDMappingService(C::BINDING_HTTP_POST, 'https://simplesamlphp.org/some/endpoint');

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($nidmsep),
        );
    }


    /**
     * Test that creating a NameIDMappingService from scratch with a ResponseLocation fails.
     */
    public function testMarshallingWithResponseLocation(): void
    {
        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:NameIDMappingService.',
        );
        new NameIDMappingService(
            C::BINDING_HTTP_POST,
            'https://simplesamlphp.org/some/endpoint',
            'https://response.location/',
        );
    }


    // test unmarshalling


    /**
     * Test creating a NameIDMappingService from XML.
     */
    public function testUnmarshalling(): void
    {
        $nidmsep = NameIDMappingService::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($nidmsep),
        );
    }


    /**
     * Test that creating a NameIDMappingService from XML fails when ResponseLocation is present.
     */
    public function testUnmarshallingWithResponseLocation(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('ResponseLocation', 'https://response.location/');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage(
            'The \'ResponseLocation\' attribute must be omitted for md:NameIDMappingService.',
        );

        NameIDMappingService::fromXML($xmlRepresentation->documentElement);
    }
}
