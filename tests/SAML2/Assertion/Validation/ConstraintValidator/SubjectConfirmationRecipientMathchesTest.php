<?php

declare(strict_types=1);

namespace SimpleSAML\Test\SAML2\Assertion\Validation\ConstraintValidator;

use PHPUnit\Framework\TestCase;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationRecipientMatches;
use SimpleSAML\SAML2\Assertion\Validation\ConstraintValidator\SubjectConfirmationResponseToMatches;
use SimpleSAML\SAML2\Assertion\Validation\Result;
use SimpleSAML\SAML2\Configuration\Destination;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmation;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationData;
use SimpleSAML\SAML2\XML\saml\SubjectConfirmationMatches;

class SubjectConfirmationRecipientMathchesTest extends TestCase
{
    /** @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmation */
    private static SubjectConfirmation $subjectConfirmation;

    /** @var \SimpleSAML\SAML2\XML\saml\SubjectConfirmationData */
    private static SubjectConfirmationData $subjectConfirmationData;


    /**
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        self::$subjectConfirmation = new SubjectConfirmation();
        self::$subjectConfirmationData = new SubjectConfirmationData();
        self::$subjectConfirmation->setSubjectConfirmationData(self::$subjectConfirmationData);
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subject_confirmation_recipient_differs_from_the_destination_the_sc_is_invalid(): void
    {
        self::$subjectConfirmation->getSubjectConfirmationData()->setRecipient('someDestination');

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('anotherDestination')
        );
        $result = new Result();

        $validator->validate(self::$subjectConfirmation, $result);

        $this->assertFalse($result->isValid());
        $this->assertCount(1, $result->getErrors());
    }


    /**
     * @group assertion-validation
     * @test
     * @return void
     */
    public function when_the_subject_confirmation_recipient_equals_the_destination_the_sc_is_invalid(): void
    {
        self::$subjectConfirmation->getSubjectConfirmationData()->setRecipient('theSameDestination');

        $validator = new SubjectConfirmationRecipientMatches(
            new Destination('theSameDestination')
        );
        $result = new Result();

        $validator->validate(self::$subjectConfirmation, $result);

        $this->assertTrue($result->isValid());
    }
}
