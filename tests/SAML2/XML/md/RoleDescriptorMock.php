<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\XML\md;

use DOMElement;
use SimpleSAML\SAML2\Constants as C;
use SimpleSAML\SAML2\XML\md\RoleDescriptor;

class RoleDescriptorMock extends RoleDescriptor
{
    public function __construct(DOMElement $xml = null)
    {
        parent::__construct('md:RoleDescriptor', $xml);
    }


    /**
     * @return DOMElement
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        $xml = parent::toXML($parent);
        $xml->setAttributeNS(C::NS_XSI, 'xsi:type', 'myns:MyElement');
        $xml->setAttributeNS('http://example.org/mynsdefinition', 'myns:tmp', 'tmp');
        $xml->removeAttributeNS('http://example.org/mynsdefinition', 'tmp');
        return $xml;
    }
}
