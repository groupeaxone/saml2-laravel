<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use Mockery;
use Mockery\MockInterface;
use SimpleSAML\SAML2\Assertion;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\NotOnOrAfter;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\Test\SAML2\AbstractControlledTimeTestCase;

/**
 * Because we're mocking a static call, we have to run it in separate processes so as to no contaminate the other
 * tests.
 *
 * @runTestsInSeparateProcesses
 */
class NotOnOrAfterTest extends AbstractControlledTimeTestCase
{
    /** @var \Mockery\MockInterface */
    private static MockInterface $assertion;


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$assertion = Mockery::mock(Assertion::class);
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function timestamp_in_the_past_before_graceperiod_is_not_valid(): void
    {
        self::$assertion->shouldReceive('getNotOnOrAfter')->andReturn($this->currentTime - 60);

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate(self::$assertion, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     */
    public function time_within_graceperiod_is_valid()
    {
        self::$assertion->shouldReceive('getNotOnOrAfter')->andReturn($this->currentTime - 59);

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate(self::$assertion, $result);

        $this->assertTrue($result->isValid());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function current_time_is_valid(): void
    {
        self::$assertion->shouldReceive('getNotOnOrAfter')->andReturn($this->currentTime);

        $validator = new NotOnOrAfter();
        $result    = new Result();

        $validator->validate(self::$assertion, $result);

        $this->assertTrue($result->isValid());
    }
}
