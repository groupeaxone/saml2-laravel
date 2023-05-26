<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\RequestedAttribute;
use SimpleSAML\SAML2\XML\saml\AttributeValue;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Test for the RequestedAttribute metadata element.
 *
 * @covers \SimpleSAML\SAML2\XML\md\RequestedAttribute
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class RequestedAttributeTest extends TestCase
{
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        self::$testedClass = RequestedAttribute::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_RequestedAttribute.xml',
        );
    }


    // test marshalling


    /**
     * Test creating a RequestedAttribute object from scratch
     */
    public function testMarshalling(): void
    {
        $ra = new RequestedAttribute(
            'attr',
            true,
            C::NAMEFORMAT_URI,
            'Attribute',
            [new AttributeValue('value1')],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($ra),
        );
    }


    /**
     * Test that creating a RequestedAttribute object from scratch works if no optional arguments are received.
     */
    public function testMarshallingWithoutOptionalArguments(): void
    {
        $ra = new RequestedAttribute('attr');
        $this->assertEquals('attr', $ra->getName());
        $this->assertNull($ra->getIsRequired());
        $this->assertNull($ra->getNameFormat());
        $this->assertNull($ra->getFriendlyName());
        $this->assertEquals([], $ra->getAttributeValues());
    }


    // test unmarshalling


    /**
     * Test creating a RequestedAttribute object from XML
     */
    public function testUnmarshalling(): void
    {
        $ra = RequestedAttribute::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($ra),
        );
    }


    /**
     * Test that creating a RequestedAttribute object from XML works when isRequired is missing.
     */
    public function testUnmarshallingWithoutIsRequired(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->removeAttribute('isRequired');
        $ra = RequestedAttribute::fromXML($xmlRepresentation->documentElement);
        $this->assertNull($ra->getIsRequired());
    }


    /**
     * Test that creating a RequestedAttribute object from XML fails when isRequired is not boolean.
     */
    public function testUnmarshallingWithWrongIsRequired(): void
    {
        $xmlRepresentation = clone self::$xmlRepresentation;
        $xmlRepresentation->documentElement->setAttribute('isRequired', 'wrong');

        $this->expectException(AssertionFailedException::class);
        $this->expectExceptionMessage('The \'isRequired\' attribute of md:RequestedAttribute must be a boolean.');

        RequestedAttribute::fromXML($xmlRepresentation->documentElement);
    }
}
