<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use SimpleSAML\SAML2\Assertíon;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\Test\SAML2\AbstractControlledTimeTestCase;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @covers \SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SessionNotOnOrAfter
 * @package simplesamlphp/saml2
 *
 * @runTestsInSeparateProcesses
 */
final class SessionNotOnOrAfterTest extends AbstractControlledTimeTestCase
{
    /**
     * @var \Mockery\MockInterface
     */
    private $assertion;


    /**
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->assertion = Mockery::mock(Assertion::class);
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function timestampInThePastBeforeGraceperiodIsNotValid(): void
    {
        $this->assertion->shouldReceive('getSessionNotOnOrAfter')->andReturn($this->currentTime - 60);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function timeWithinGraceperiodIsValid(): void
    {
        $this->assertion->shouldReceive('getSessionNotOnOrAfter')->andReturn($this->currentTime - 59);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function currentTimeIsValid(): void
    {
        $this->assertion->shouldReceive('getSessionNotOnOrAfter')->andReturn($this->currentTime);

        $validator = new SessionNotOnOrAfter();
        $result    = new Result();

        $validator->validate($this->assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
