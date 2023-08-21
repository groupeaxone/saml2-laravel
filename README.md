# SimpleSAMLphp SAML2 library

[![Build Status](https://travis-ci.org/simplesamlphp/saml2.png?branch=feature/fix-build)](https://travis-ci.org/simplesamlphp/saml2) [![Coverage Status](https://img.shields.io/coveralls/simplesamlphp/saml2.svg)](https://coveralls.io/r/simplesamlphp/saml2)

A PHP library for SAML2 related functionality. Extracted from [SimpleSAMLphp](https://www.simplesamlphp.org),
used by [OpenConext](https://www.openconext.org).
This library started as a collaboration between [UNINETT](https://www.uninett.no) and [SURFnet](https://www.surfnet.nl) but everyone is invited to contribute.

## Before you use it

**DO NOT USE THIS LIBRARY UNLESS YOU ARE INTIMATELY FAMILIAR WITH THE SAML2 SPECIFICATION.**

If you are not familiar with the SAML2 specification and are simply looking to connect your application using SAML2,
you should probably use [SimpleSAMLphp](https://www.simplesamlphp.org).

Note that the **HTTP Artifact Binding and SOAP client do not work** outside of SimpleSAMLphp.

## Which version to pick?

The latest released version (`5.x` range) is the _only supported version_.

All other branches (`4.x` and earlier) are no longer supported and will not receive any maintenance or
(security) fixes. Do not use these versions.

Also be sure to check the [UPGRADING.md](UPGRADING.md) file if you are upgrading from an older version to `>= 5.x`. Here
you will find instructions on how to deal with BC breaking changes between versions.

## Usage

* Install with [Composer](https://getcomposer.org/doc/00-intro.md), run the following command in your project:

```bash
composer require simplesamlphp/saml2:^5.0
```

* Provide the required external dependencies by extending and implementing the ```SAML2\Compat\AbstractContainer```
  then injecting it in the ContainerSingleton (see example below).

* **Make sure you've read the security section below**.

* Use at will.

Example:

```php
// Use Composers autoloading
require 'vendor/autoload.php';

// Implement the Container interface (out of scope for example)
require 'container.php';
SimpleSAML\SAML2\Compat\ContainerSingleton::setContainer($container);

// Set up an AuthnRequest
$id = $container->generateId();
$issuer = new SimpleSAML\SAML2\XML\saml\Issuer('https://sp.example.edu');
$destination = 'https://idp.example.edu';
$request = new SimpleSAML\SAML2\XML\samlp\AuthnRequest(
    id: $id,
    issuer: $issuer,
    destination: $destination,
);


// Send it off using the HTTP-Redirect binding
$binding = new SimpleSAML\SAML2\HTTPRedirect();
$binding->send($request);
```

## Security

* Should you need to create a DOMDocument instance, use the `SimpleSAML\XML\DOMDocumentFactory` to create DOMDocuments from
  either a string (`SimpleSAML\XML\DOMDocumentFactory::fromString($theXmlAsString)`), a file (`SimpleSAML\XML\DOMDocumentFactory::fromFile($pathToTheFile)`)
  or just a new instance (`SimpleSAML\XML\DOMDocumentFactory::create()`). This in order to protect yourself against the
  [XXE Processing Vulnerability](https://www.owasp.org/index.php/XML_External_Entity_(XXE)_Processing), as well as
  [XML Entity Expansion](https://phpsecurity.readthedocs.org/en/latest/Injection-Attacks.html#defenses-against-xml-entity-expansion) attacks

## License

This library is licensed under the LGPL license version 2.1.
For more details see [LICENSE](https://raw.github.com/simplesamlphp/saml2/master/LICENSE).
