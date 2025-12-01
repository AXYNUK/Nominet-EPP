<?php

/**
 * Mock Registrar_AdapterAbstract for testing
 * 
 * This mock class simulates FOSSBilling's Registrar_AdapterAbstract
 * for unit testing without requiring the full FOSSBilling installation.
 */
abstract class Registrar_AdapterAbstract
{
    /**
     * @var array Configuration options
     */
    protected $config = [];

    /**
     * @var MockLogger Logger instance
     */
    protected $log;

    /**
     * Constructor
     *
     * @param array $options Configuration options
     */
    public function __construct($options = [])
    {
        $this->log = new MockLogger();
    }

    /**
     * Get configuration form definition
     *
     * @return array
     */
    abstract public static function getConfig(): array;

    /**
     * Check if domain is available for registration
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function isDomainAvailable(Registrar_Domain $domain);

    /**
     * Check if domain can be transferred
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function isDomainCanBeTransferred(Registrar_Domain $domain);

    /**
     * Modify domain nameservers
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function modifyNs(Registrar_Domain $domain);

    /**
     * Modify domain contact information
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function modifyContact(Registrar_Domain $domain);

    /**
     * Transfer domain
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function transferDomain(Registrar_Domain $domain);

    /**
     * Get domain details
     *
     * @param Registrar_Domain $domain
     * @return Registrar_Domain
     */
    abstract public function getDomainDetails(Registrar_Domain $domain);

    /**
     * Delete domain
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function deleteDomain(Registrar_Domain $domain);

    /**
     * Register domain
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function registerDomain(Registrar_Domain $domain);

    /**
     * Renew domain
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function renewDomain(Registrar_Domain $domain);

    /**
     * Enable privacy protection
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function enablePrivacyProtection(Registrar_Domain $domain);

    /**
     * Disable privacy protection
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function disablePrivacyProtection(Registrar_Domain $domain);

    /**
     * Get EPP/auth code
     *
     * @param Registrar_Domain $domain
     * @return string
     */
    abstract public function getEpp(Registrar_Domain $domain);

    /**
     * Lock domain
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function lock(Registrar_Domain $domain);

    /**
     * Unlock domain
     *
     * @param Registrar_Domain $domain
     * @return bool
     */
    abstract public function unlock(Registrar_Domain $domain);

    /**
     * Get logger instance
     *
     * @return MockLogger
     */
    public function getLog(): MockLogger
    {
        if ($this->log === null) {
            $this->log = new MockLogger();
        }
        return $this->log;
    }

    /**
     * Set logger instance
     *
     * @param MockLogger $log
     * @return void
     */
    public function setLog(MockLogger $log): void
    {
        $this->log = $log;
    }
}

/**
 * Mock Logger for testing
 */
class MockLogger
{
    private $logs = [];

    public function debug($message): void
    {
        $this->logs[] = ['level' => 'debug', 'message' => $message];
    }

    public function info($message): void
    {
        $this->logs[] = ['level' => 'info', 'message' => $message];
    }

    public function warning($message): void
    {
        $this->logs[] = ['level' => 'warning', 'message' => $message];
    }

    public function err($message): void
    {
        $this->logs[] = ['level' => 'error', 'message' => $message];
    }

    public function error($message): void
    {
        $this->err($message);
    }

    /**
     * Get all logged messages
     *
     * @return array
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * Clear all logs
     *
     * @return void
     */
    public function clear(): void
    {
        $this->logs = [];
    }

    /**
     * Get logs filtered by level
     *
     * @param string $level
     * @return array
     */
    public function getLogsByLevel(string $level): array
    {
        return array_filter($this->logs, fn($log) => $log['level'] === $level);
    }
}
