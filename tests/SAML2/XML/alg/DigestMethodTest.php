<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\alg;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\Utils;
use SimpleSAML\SAML2\XML\alg\DigestMethod;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\DOMDocumentFactory;
use SimpleSAML\XML\Exception\MissingAttributeException;
use SimpleSAML\XML\TestUtils\SerializableElementTestTrait;
use SimpleSAML\XML\TestUtils\SchemaValidationTestTrait;

use function dirname;
use function strval;

/**
 * Class \SAML2\XML\alg\DigestMethodTest
 *
 * @covers \SimpleSAML\SAML2\XML\alg\AbstractAlgElement
 * @covers \SimpleSAML\SAML2\XML\alg\DigestMethod
 *
 * @package simplesamlphp/saml2
 */
final class DigestMethodTest extends TestCase
{
    use SerializableElementTestTrait;
    use SchemaValidationTestTrait;


    /**
     */
    public static function setUpBeforeClass(): void
    {
        self::$schemaFile = dirname(__FILE__, 5)
            . '/resources/schemas/sstc-saml-metadata-algsupport-v1.0.xsd';

        self::$testedClass = DigestMethod::class;

        self::$xmlRepresentation = DOMDocumentFactory::fromFile(
            dirname(__FILE__, 4) . '/resources/xml/alg_DigestMethod.xml'
        );
    }


    /**
     */
    public function testMarshalling(): void
    {
        $digestMethod = new DigestMethod(
            C::DIGEST_SHA256,
            [
                new Chunk(DOMDocumentFactory::fromString(
                    '<ssp:Chunk xmlns:ssp="urn:x-simplesamlphp:namespace">Some</ssp:Chunk>'
                )->documentElement)
            ],
        );

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($digestMethod),
        );
    }


    /**
     */
    public function testUnmarshalling(): void
    {
        $digestMethod = DigestMethod::fromXML(self::$xmlRepresentation->documentElement);

        $this->assertEquals(
            self::$xmlRepresentation->saveXML(self::$xmlRepresentation->documentElement),
            strval($digestMethod),
        );
    }


    /**
     */
    public function testUnmarshallingMissingAlgorithmThrowsException(): void
    {
        $document = clone self::$xmlRepresentation->documentElement;
        $document->removeAttribute('Algorithm');

        $this->expectException(MissingAttributeException::class);
        $this->expectExceptionMessage("Missing 'Algorithm' attribute on alg:DigestMethod.");

        DigestMethod::fromXML($document);
    }
}
