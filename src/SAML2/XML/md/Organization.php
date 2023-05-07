<?php

declare(strict_types=1);

namespace SAML2\XML\md;

use DOMElement;
use SAML2\Constants as C;
use SimpleSAML\Assert\Assert;
use SimpleSAML\XML\Chunk;
use SimpleSAML\XML\Utils as XMLUtils;

/**
 * Class representing SAML 2 Organization element.
 *
 * @package SimpleSAMLphp
 */
class Organization
{
    /**
     * Extensions on this element.
     *
     * Array of extension elements.
     *
     * @var array
     */
    private array $Extensions = [];

    /**
     * The OrganizationName, as an array of language => translation.
     *
     * @var array
     */
    private array $OrganizationName = [];

    /**
     * The OrganizationDisplayName, as an array of language => translation.
     *
     * @var array
     */
    private array $OrganizationDisplayName = [];

    /**
     * The OrganizationURL, as an array of language => translation.
     *
     * @var array
     */
    private array $OrganizationURL = [];


    /**
     * Initialize an Organization element.
     *
     * @param \DOMElement|null $xml The XML element we should load.
     */
    public function __construct(DOMElement $xml = null)
    {
        if ($xml === null) {
            return;
        }

        $this->Extensions = Extensions::getList($xml);

        $this->OrganizationName = XMLUtils::extractLocalizedStrings($xml, C::NS_MD, 'OrganizationName');

        $this->OrganizationDisplayName = XMLUtils::extractLocalizedStrings(
            $xml,
            C::NS_MD,
            'OrganizationDisplayName'
        );

        $this->OrganizationURL = XMLUtils::extractLocalizedStrings($xml, C::NS_MD, 'OrganizationURL');
    }


    /**
     * Collect the value of the Extensions property.
     *
     * @return \SimpleSAML\XML\Chunk[]
     */
    public function getExtensions(): array
    {
        return $this->Extensions;
    }


    /**
     * Set the value of the Extensions property.
     *
     * @param array $extensions
     * @return void
     */
    public function setExtensions(array $extensions): void
    {
        $this->Extensions = $extensions;
    }


    /**
     * Add an Extension.
     *
     * @param \SimpleSAML\XML\Chunk $extensions The Extensions
     * @return void
     */
    public function addExtension(Extensions $extension): void
    {
        $this->Extensions[] = $extension;
    }


    /**
     * Collect the value of the OrganizationName property.
     *
     * @return string[]
     */
    public function getOrganizationName(): array
    {
        return $this->OrganizationName;
    }


    /**
     * Set the value of the OrganizationName property.
     *
     * @param array $organizationName
     * @return void
     */
    public function setOrganizationName(array $organizationName): void
    {
        $this->OrganizationName = $organizationName;
    }


    /**
     * Collect the value of the OrganizationDisplayName property.
     *
     * @return string[]
     */
    public function getOrganizationDisplayName(): array
    {
        return $this->OrganizationDisplayName;
    }


    /**
     * Set the value of the OrganizationDisplayName property.
     *
     * @param array $organizationDisplayName
     * @return void
     */
    public function setOrganizationDisplayName(array $organizationDisplayName): void
    {
        $this->OrganizationDisplayName = $organizationDisplayName;
    }


    /**
     * Collect the value of the OrganizationURL property.
     *
     * @return string[]
     */
    public function getOrganizationURL(): array
    {
        return $this->OrganizationURL;
    }


    /**
     * Set the value of the OrganizationURL property.
     *
     * @param array $organizationURL
     * @return void
     */
    public function setOrganizationURL(array $organizationURL): void
    {
        $this->OrganizationURL = $organizationURL;
    }


    /**
     * Convert this Organization to XML.
     *
     * @param  \DOMElement $parent The element we should add this organization to.
     * @return \DOMElement This Organization-element.
     */
    public function toXML(DOMElement $parent): DOMElement
    {
        Assert::notEmpty($this->OrganizationName);
        Assert::notEmpty($this->OrganizationDisplayName);
        Assert::notEmpty($this->OrganizationURL);

        $doc = $parent->ownerDocument;

        $e = $doc->createElementNS(C::NS_MD, 'md:Organization');
        $parent->appendChild($e);

        Extensions::addList($e, $this->Extensions);

        XMLUtils::addStrings($e, C::NS_MD, 'md:OrganizationName', true, $this->OrganizationName);
        XMLUtils::addStrings($e, C::NS_MD, 'md:OrganizationDisplayName', true, $this->OrganizationDisplayName);
        XMLUtils::addStrings($e, C::NS_MD, 'md:OrganizationURL', true, $this->OrganizationURL);

        return $e;
    }
}
