<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\AdditionalMetadataLocation;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\md\AdditionalMetadataLocationTest
 */
class AdditionalMetadataLocationTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $document = DOMDocumentFactory::fromString('<root/>');

        $additionalMetadataLocation = new AdditionalMetadataLocation();
        $additionalMetadataLocation->setNamespace('NamespaceAttribute');
        $additionalMetadataLocation->setLocation('TheLocation');
        $additionalMetadataLocationElement = $additionalMetadataLocation->toXML($document->firstChild);

        $xpCache = XPath::getXPath($additionalMetadataLocationElement);
        $additionalMetadataLocationElements = XPath::xpQuery(
            $additionalMetadataLocationElement,
            '/root/saml_metadata:AdditionalMetadataLocation',
            $xpCache,
        );
        $this->assertCount(1, $additionalMetadataLocationElements);
        $additionalMetadataLocationElement = $additionalMetadataLocationElements[0];

        $this->assertEquals('TheLocation', $additionalMetadataLocationElement->textContent);
        $this->assertEquals('NamespaceAttribute', $additionalMetadataLocationElement->getAttribute("namespace"));
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $document = DOMDocumentFactory::fromString(
            '<md:AdditionalMetadataLocation xmlns:md="' . C::NS_MD . '"' .
            ' namespace="TheNamespaceAttribute">LocationText</md:AdditionalMetadataLocation>'
        );
        $additionalMetadataLocation = new AdditionalMetadataLocation($document->firstChild);
        $this->assertEquals('TheNamespaceAttribute', $additionalMetadataLocation->getNamespace());
        $this->assertEquals('LocationText', $additionalMetadataLocation->getLocation());

        $document->loadXML(
            '<md:AdditionalMetadataLocation xmlns:md="' . C::NS_MD . '"' .
            '>LocationText</md:AdditionalMetadataLocation>'
        );
        $this->expectException(Exception::class, 'Missing namespace attribute on AdditionalMetadataLocation element.');
        new AdditionalMetadataLocation($document->firstChild);
    }
}
