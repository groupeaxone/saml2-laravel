<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\saml;

use Exception;
use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\NameID;
use SimpleSAML\SAML2\Utils\XPath;
use SimpleSAML\XML\DOMDocumentFactory;

/**
 * Class \SimpleSAML\SAML2\XML\saml\SubjectConfirmationTest
 */
class SubjectConfirmationTest extends TestCase
{
    /**
     * @return void
     */
    public function testMarshalling(): void
    {
        $nameId = new NameID();
        $nameId->setValue('SomeNameIDValue');

        $subjectConfirmation = new SubjectConfirmation();
        $subjectConfirmation->setMethod('SomeMethod');
        $subjectConfirmation->setNameID($nameId);
        $subjectConfirmation->setSubjectConfirmationData(new SubjectConfirmationData());

        $document = DOMDocumentFactory::fromString('<root />');
        $subjectConfirmationElement = $subjectConfirmation->toXML($document->firstChild);
        $xpCache = XPath::getXPath($subjectConfirmationElement);
        $subjectConfirmationElements = XPath::xpQuery(
            $subjectConfirmationElement,
            '//saml_assertion:SubjectConfirmation',
            $xpCache
        );
        $this->assertCount(1, $subjectConfirmationElements);
        /** @var \DOMElement $subjectConfirmationElement */
        $subjectConfirmationElement = $subjectConfirmationElements[0];

        $this->assertEquals('SomeMethod', $subjectConfirmationElement->getAttribute("Method"));
        $this->assertCount(1, XPath::xpQuery($subjectConfirmationElement, "./saml_assertion:NameID", $xpCache));
        $this->assertCount(
            1,
            XPath::xpQuery($subjectConfirmationElement, "./saml_assertion:SubjectConfirmationData", $xpCache)
        );
    }


    /**
     * @return void
     */
    public function testUnmarshalling(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );

        $subjectConfirmation = new SubjectConfirmation($document->firstChild);
        $this->assertEquals('SomeMethod', $subjectConfirmation->getMethod());
        $this->assertTrue($subjectConfirmation->getNameID() instanceof NameID);
        $this->assertEquals('SomeNameIDValue', $subjectConfirmation->getNameID()->getValue());
        $this->assertTrue($subjectConfirmation->getSubjectConfirmationData() instanceof SubjectConfirmationData);
    }


    /**
     * @return void
     */
    public function testMethodMissingThrowsException(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('SubjectConfirmation element without Method attribute');
        $subjectConfirmation = new SubjectConfirmation($document->firstChild);
    }


    /**
     * @return void
     */
    public function testManyNameIDThrowsException(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:NameID>AnotherNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData/>
</saml:SubjectConfirmation>
XML
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('More than one NameID in a SubjectConfirmation element');
        $subjectConfirmation = new SubjectConfirmation($document->firstChild);
    }


    /**
     * @return void
     */
    public function testManySubjectConfirmationDataThrowsException(): void
    {
        $samlNamespace = C::NS_SAML;
        $document = DOMDocumentFactory::fromString(<<<XML
<saml:SubjectConfirmation xmlns:saml="{$samlNamespace}" Method="SomeMethod">
  <saml:NameID>SomeNameIDValue</saml:NameID>
  <saml:SubjectConfirmationData Recipient="Me" />
  <saml:SubjectConfirmationData Recipient="Someone Else" />
</saml:SubjectConfirmation>
XML
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'More than one SubjectConfirmationData child in a SubjectConfirmation element'
        );
        $subjectConfirmation = new SubjectConfirmation($document->firstChild);
    }
}
