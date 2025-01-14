Notes for the SimpleSAMLphp SAML2 developer
===========================================

Coding standard
---------------
PSR-0, PSR-1 and PSR-12.
Test with the PHPCS configuration in tools/phpcs/ruleset.xml
(note if you have PHPStorm you can use this to turn on the PHPCS inspection).


Testing
-------
Use PHPUnit for Unit Testing.
Test with the 2 known users: (SimpleSAMLphp)[https://www.simplesamlphp.org] and
(OpenConext-engineblock)[https://www.openconext.org] .

### Using Tests in Development

In order to run the unittests, use `vendor/bin/phpunit -c tools/phpunit`

Contributing
------------
Prior to contributing, please read the following documentation:
- [Background][2]
- [Technical Design][1]

Also when introducing a BC breaking change, please update the [UPGRADING.md](UPGRADING.md) with instructions on how to deal with the change.

[1]: https://github.com/simplesamlphp/saml2/wiki/SAML2-v1.0-Technical-Design
[2]: https://github.com/simplesamlphp/saml2/wiki/Background
