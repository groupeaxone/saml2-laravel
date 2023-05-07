<?php

declare(strict_types=1);

namespace SAML2\Compat;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class \SAML2\Compat\MockContainer
 */
class MockContainer extends AbstractContainer
{
    /**
     * @var string
     */
    private string $id = '123';

    /**
     * @var array
     */
    private array $debugMessages = [];


    /**
     * Get a PSR-3 compatible logger.
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return new NullLogger();
    }


    /**
     * Generate a random identifier for identifying SAML2 documents.
     * @return string
     */
    public function generateId(): string
    {
        return $this->id;
    }


    /**
     * Log an incoming message to the debug log.
     *
     * Type can be either:
     * - **in** XML received from third party
     * - **out** XML that will be sent to third party
     * - **encrypt** XML that is about to be encrypted
     * - **decrypt** XML that was just decrypted
     *
     * @param \DOMElement|string $message
     * @param string $type
     * @return void
     */
    public function debugMessage($message, string $type): void
    {
        $this->debugMessages[$type] = $message;
    }


    /**
     * Trigger the user to perform a POST to the given URL with the given data.
     *
     * @param string|null $url
     * @param array $data
     * @return string
     */
    public function getPostRedirectURL(
        /** @scrutinizer ignore-unused */string $url = null,
        /** @scrutinizer ignore-unused */array $data = []
    ): string {
        return $url;
    }


    /**
     * @return string
     */
    public function getTempDir(): string
    {
        return sys_get_temp_dir();
    }


    /**
     * @param string $filename
     * @param string $data
     * @param int|null $mode
     * @return void
     */
    public function writeFile(string $filename, string $data, int $mode = null): void
    {
        if ($mode === null) {
            $mode = 0600;
        }
        file_put_contents($filename, $data);
        chmod($filename, $mode);
    }
}
