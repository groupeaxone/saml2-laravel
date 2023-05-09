<?php

declare(strict_types=1);

namespace SimpleSAML\SAML2\Signature;

use Exception;
use Psr\Log\LoggerInterface;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use SimpleSAML\SAML2\SignedElement;
use SimpleSAML\SAML2\Utilities\ArrayCollection;

use function sprintf;

abstract class AbstractChainedValidator implements ChainedValidator
{
    /**
     * Constructor for AbstractChainedValidator
     *
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected LoggerInterface $logger
    ) {
    }


    /**
     * BC compatible version of the signature check
     *
     * @param \SimpleSAML\SAML2\SignedElement      $element
     * @param \SimpleSAML\SAML2\Utilities\ArrayCollection $pemCandidates
     *
     * @throws \Exception
     *
     * @return bool
     */
    protected function validateElementWithKeys(
        SignedElement $element,
        ArrayCollection $pemCandidates
    ): bool {
        $lastException = null;
        foreach ($pemCandidates as $index => $candidateKey) {
            $key = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'public']);
            $key->loadKey($candidateKey->getCertificate());

            try {
                /*
                 * Make sure that we have a valid signature on either the response or the assertion.
                 */
                $result = $element->validate($key);
                if ($result) {
                    $this->logger->debug(sprintf('Validation with key "#%d" succeeded', $index));
                    return true;
                }
                $this->logger->debug(sprintf('Validation with key "#%d" failed without exception.', $index));
            } catch (Exception $e) {
                $this->logger->debug(sprintf(
                    'Validation with key "#%d" failed with exception: %s',
                    $index,
                    $e->getMessage()
                ));

                $lastException = $e;
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        } else {
            return false;
        }
    }
}
