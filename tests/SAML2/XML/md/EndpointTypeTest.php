<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\Assert\AssertionFailedException;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\AssertionIDRequestService;
use SimpleSAML\SAML2\XML\md\AttributeService;
use SimpleSAML\XML\Attribute as XMLAttribute;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\InvalidDOMElementException;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\Exception\SchemaViolationException;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;
use SimpleSAML\XML\TestUtils\ArrayizableElementTestTrait;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\md\EndpointTypeTest
 *
 * @covers \SimpleSAML\SAML2\XML\md\AbstractEndpointType
 * @covers \SimpleSAML\SAML2\XML\md\AbstractMdElement
 * @package simplesamlphp/saml2
 */
final class EndpointTypeTest extends TestCase
{
    use ArrayizableElementTestTrait;
    use SchemaValidationTestTrait;
    use SerializableElementTestTrait;

    /** @var \DOMDocument */
    protected DOMDocument $ext;


    /**
     */
    protected function setUp(): void
    {
        $this->ext = DOMDocumentFactory::fromString(
            '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Some</ssp:Chunk>',
        );

        $this->schema = dirname(__FILE__, 5) . '/resources/schemas/saml-schema-metadata-2.0.xsd';

        $this->testedClass = AttributeService::class;

        $this->arrayRepresentation = [
            'Binding' => C::BINDING_HTTP_POST,
            'Location' => 'https://whatever/',
            'ResponseLocation' => 'https://foo.bar/',
            'Extensions' => [new Chunk($this->ext->documentElement)],
            'attributes' => [(new XMLAttribute('urn:x-simplesamlphp:namespace', 'test', 'attr', 'value'))->toArray()],
        ];

        $this->xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/md_AttributeService.xml',
        );
    }


    // test marshalling


    /**
     * Test creating an EndpointType from scratch.
     */
    public function testMarshalling(): void
    {
        $attr = new XMLAttribute('urn:x-simplesamlphp:namespace', 'test', 'attr', 'value');

        $endpointType = new AttributeService(
            C::BINDING_HTTP_POST,
            'https://whatever/',
            'https://foo.bar/',
            [$attr],
            [new Chunk($this->ext->documentElement)],
        );

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($endpointType),
        );
    }


    /**
     * Test that creating an EndpointType from scratch with an empty Binding fails.
     */
    public function testMarshallingWithEmptyBinding(): void
    {
        $this->expectException(SchemaViolationException::class);
        new AttributeService('', 'https://simplesamlphp.org/some/endpoint');
    }


    /**
     * Test that creating an EndpointType from scratch with an empty Location fails.
     */
    public function testMarshallingWithEmptyLocation(): void
    {
        $this->expectException(SchemaViolationException::class);
        new AttributeService(C::BINDING_HTTP_POST, '');
    }


    /**
     * Test that creating an EndpointType from scratch without optional attributes works.
     */
    public function testMarshallingWithoutOptionalAttributes(): void
    {
        $endpointType = new AttributeService(C::BINDING_HTTP_POST, 'https://simplesamlphp.org/some/endpoint');
        $this->assertNull($endpointType->getResponseLocation());
        $this->assertEmpty($endpointType->getAttributesNS());
    }


    // test unmarshalling


    /**
     * Test creating an EndpointType from XML.
     */
    public function testUnmarshalling(): void
    {
        $endpointType = AttributeService::fromXML($this->xmlRepresentation->documentElement);

        $this->assertEquals(
            $this->xmlRepresentation->saveXML($this->xmlRepresentation->documentElement),
            strval($endpointType),
        );
    }


    /**
     * Test that creating an EndpointType from XML checks the actual name of the endpoint.
     */
    public function testUnmarshallingUnexpectedEndpoint(): void
    {
        $this->expectException(InvalidDOMElementException::class);
        $this->expectExceptionMessage(
            'Unexpected name for endpoint: AttributeService. Expected: AssertionIDRequestService.',
        );
        AssertionIDRequestService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without a Binding attribute fails.
     */
    public function testUnmarshallingWithoutBinding(): void
    {
        $this->xmlRepresentation->documentElement->removeAttribute('Binding');
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'Binding\' attribute on md:AttributeService.');
        AttributeService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML with an empty Binding attribute fails.
     */
    public function testUnmarshallingWithEmptyBinding(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('Binding', '');
        $this->expectException(SchemaViolationException::class);
        AttributeService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without a Location attribute fails.
     */
    public function testUnmarshallingWithoutLocation(): void
    {
        $this->xmlRepresentation->documentElement->removeAttribute('Location');
        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage('Missing \'Location\' attribute on md:AttributeService.');
        AttributeService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML with an empty Location attribute fails.
     */
    public function testUnmarshallingWithEmptyLocation(): void
    {
        $this->xmlRepresentation->documentElement->setAttribute('Location', '');
        $this->expectException(SchemaViolationException::class);
        AttributeService::fromXML($this->xmlRepresentation->documentElement);
    }


    /**
     * Test that creating an EndpointType from XML without the optional attributes works.
     */
    public function testUnmarshallingWithoutOptionalAttributes(): void
    {
        $mdNamespace = C::NS_MD;
        $location = 'https://simplesamlphp.org/some/endpoint';

        $document = DOMDocumentFactory::fromString(<<<XML
<md:AttributeService xmlns:md="{$mdNamespace}" Binding="urn:x-simplesamlphp:namespace" Location="{$location}" />
XML
        );
        $as = AttributeService::fromXML($document->documentElement);
        $this->assertNull($as->getResponseLocation());
        $this->assertEmpty($as->getAttributesNS());
    }
}
