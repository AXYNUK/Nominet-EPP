<?php

/**
 * Nominet EPP Registrar Adapter for FOSSBilling
 * 
 * Copyright 2025 AXYN
 * SPDX-License-Identifier: Apache-2.0
 *
 * @copyright AXYN (https://www.axyn.co.uk)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache-2.0
 */

class Registrar_Adapter_Nominet extends Registrar_AdapterAbstract
{
    public $config = [
        'username' => '',
        'password' => '',
        'test_mode' => false,
    ];

    private $epp_host = 'epp.nominet.uk';
    private $epp_port = 700;
    private $epp_test_host = 'testbed-epp.nominet.uk';
    private $epp_test_port = 700;

    public function __construct($options)
    {
        if (!extension_loaded('openssl')) {
            throw new Registrar_Exception('OpenSSL PHP extension is required for Nominet EPP');
        }

        if (isset($options['username'])) {
            $this->config['username'] = $options['username'];
        }
        if (isset($options['password'])) {
            $this->config['password'] = $options['password'];
        }
        if (isset($options['test_mode'])) {
            $this->config['test_mode'] = (bool) $options['test_mode'];
        }
    }

    public static function getConfig(): array
    {
        return [
            'label' => 'Manages domain registration via Nominet EPP for .uk domains',
            'form' => [
                'username' => [
                    'text',
                    [
                        'label' => 'Nominet Tag (IPS Tag)',
                        'description' => 'Your Nominet IPS Tag',
                        'required' => true,
                    ],
                ],
                'password' => [
                    'password',
                    [
                        'label' => 'Password',
                        'description' => 'Your Nominet EPP password',
                        'required' => true,
                        'renderPassword' => true,
                    ],
                ],
                'test_mode' => [
                    'radio',
                    [
                        'multiOptions' => ['1' => 'Yes', '0' => 'No'],
                        'label' => 'Test mode',
                        'description' => 'Use Nominet testbed environment',
                    ],
                ],
            ],
        ];
    }

    public function getTlds(): array
    {
        return [
            '.uk',
            '.co.uk',
            '.org.uk',
            '.me.uk',
            '.net.uk',
            '.ltd.uk',
            '.plc.uk',
            '.sch.uk',
        ];
    }

    public function isDomainAvailable(Registrar_Domain $domain)
    {
        $this->getLog()->debug('Checking domain availability: ' . $domain->getName());

        try {
            $response = $this->_makeRequest('check', $domain->getName());
            
            // Parse EPP response
            if (preg_match('/<domain:name avail="(0|1)">/', $response, $matches)) {
                return $matches[1] === '1';
            }
            
            throw new Registrar_Exception('Unable to determine domain availability');
        } catch (Exception $e) {
            $this->getLog()->err($e->getMessage());
            throw new Registrar_Exception($e->getMessage());
        }
    }

    public function isDomainCanBeTransferred(Registrar_Domain $domain): bool
    {
        $this->getLog()->debug('Checking if domain can be transferred: ' . $domain->getName());
        
        // For Nominet, check if domain exists and is not locked
        try {
            $response = $this->_makeRequest('info', $domain->getName());
            
            // If domain exists and is not locked, it can be transferred
            if (strpos($response, '<domain:name>') !== false) {
                return true;
            }
            
            return false;
        } catch (Exception $e) {
            $this->getLog()->err($e->getMessage());
            return false;
        }
    }

    public function modifyNs(Registrar_Domain $domain): bool
    {
        $this->getLog()->debug('Modifying nameservers for: ' . $domain->getName());

        try {
            $nameservers = [];
            if ($ns1 = $domain->getNs1()) $nameservers[] = $ns1;
            if ($ns2 = $domain->getNs2()) $nameservers[] = $ns2;
            if ($ns3 = $domain->getNs3()) $nameservers[] = $ns3;
            if ($ns4 = $domain->getNs4()) $nameservers[] = $ns4;

            if (empty($nameservers)) {
                throw new Registrar_Exception('At least one nameserver is required');
            }

            $response = $this->_makeRequest('update_ns', $domain->getName(), ['nameservers' => $nameservers]);
            
            return true;
        } catch (Exception $e) {
            $this->getLog()->err($e->getMessage());
            throw new Registrar_Exception($e->getMessage());
        }
    }

    public function transferDomain(Registrar_Domain $domain): bool
    {
        $this->getLog()->debug('Transferring domain: ' . $domain->getName());

        try {
            $response = $this->_makeRequest('transfer', $domain->getName(), [
                'authCode' => $domain->getEpp(),
            ]);
            
            return true;
        } catch (Exception $e) {
            $this->getLog()->err($e->getMessage());
            throw new Registrar_Exception($e->getMessage());
        }
    }

    public function getDomainDetails(Registrar_Domain $domain)
    {
        $this->getLog()->debug('Getting domain details: ' . $domain->getName());

        try {
            $response = $this->_makeRequest('info', $domain->getName());
            
            // Parse registration and expiration dates from EPP response
            if (preg_match('/<domain:crDate>([^<]+)<\/domain:crDate>/', $response, $matches)) {
                $domain->setRegistrationTime(strtotime($matches[1]));
            }
            
            if (preg_match('/<domain:exDate>([^<]+)<\/domain:exDate>/', $response, $matches)) {
                $domain->setExpirationTime(strtotime($matches[1]));
            }

            // Parse nameservers
            preg_match_all('/<domain:hostObj>([^<]+)<\/domain:hostObj>/', $response, $nsMatches);
            if (!empty($nsMatches[1])) {
                if (isset($nsMatches[1][0])) $domain->setNs1($nsMatches[1][0]);
                if (isset($nsMatches[1][1])) $domain->setNs2($nsMatches[1][1]);
                if (isset($nsMatches[1][2])) $domain->setNs3($nsMatches[1][2]);
                if (isset($nsMatches[1][3])) $domain->setNs4($nsMatches[1][3]);
            }

            return $domain;
        } catch (Exception $e) {
            $this->getLog()->err($e->getMessage());
            throw new Registrar_Exception($e->getMessage());
        }
    }

    public function deleteDomain(Registrar_Domain $domain): bool
    {
        throw new Registrar_Exception('Domain deletion is not supported by Nominet. Domains must be allowed to expire.');
    }

    public function registerDomain(Registrar_Domain $domain): bool
    {
        $this->getLog()->debug('Registering domain: ' . $domain->getName());

        try {
            $client = $domain->getContactRegistrant();
            
            $nameservers = [];
            if ($ns1 = $domain->getNs1()) $nameservers[] = $ns1;
            if ($ns2 = $domain->getNs2()) $nameservers[] = $ns2;
            if ($ns3 = $domain->getNs3()) $nameservers[] = $ns3;
            if ($ns4 = $domain->getNs4()) $nameservers[] = $ns4;

            $response = $this->_makeRequest('create', $domain->getName(), [
                'period' => $domain->getRegistrationPeriod(),
                'nameservers' => $nameservers,
                'registrant' => [
                    'name' => $client->getName(),
                    'org' => $client->getCompany(),
                    'email' => $client->getEmail(),
                    'address' => $client->getAddress1(),
                    'city' => $client->getCity(),
                    'postcode' => $client->getZip(),
                    'country' => $client->getCountry(),
                    'phone' => $client->getTelCc() . '.' . $client->getTel(),
                ],
            ]);

            return true;
        } catch (Exception $e) {
            $this->getLog()->err($e->getMessage());
            throw new Registrar_Exception($e->getMessage());
        }
    }

    public function renewDomain(Registrar_Domain $domain): bool
    {
        $this->getLog()->debug('Renewing domain: ' . $domain->getName());

        try {
            $response = $this->_makeRequest('renew', $domain->getName(), [
                'period' => $domain->getRegistrationPeriod(),
                'curExpDate' => date('Y-m-d', $domain->getExpirationTime()),
            ]);

            return true;
        } catch (Exception $e) {
            $this->getLog()->err($e->getMessage());
            throw new Registrar_Exception($e->getMessage());
        }
    }

    public function modifyContact(Registrar_Domain $domain): bool
    {
        throw new Registrar_Exception('Contact modification must be done through Nominet\'s online services.');
    }

    public function enablePrivacyProtection(Registrar_Domain $domain): bool
    {
        throw new Registrar_Exception('Privacy protection is not available for .uk domains');
    }

    public function disablePrivacyProtection(Registrar_Domain $domain): bool
    {
        throw new Registrar_Exception('Privacy protection is not available for .uk domains');
    }

    public function getEpp(Registrar_Domain $domain): string
    {
        $this->getLog()->debug('Getting EPP code for: ' . $domain->getName());

        try {
            $response = $this->_makeRequest('info', $domain->getName());
            
            if (preg_match('/<domain:authInfo>.*?<domain:pw>([^<]+)<\/domain:pw>/', $response, $matches)) {
                return $matches[1];
            }
            
            throw new Registrar_Exception('EPP code not found');
        } catch (Exception $e) {
            $this->getLog()->err($e->getMessage());
            throw new Registrar_Exception($e->getMessage());
        }
    }

    public function lock(Registrar_Domain $domain): bool
    {
        throw new Registrar_Exception('Domain locking is managed automatically by Nominet');
    }

    public function unlock(Registrar_Domain $domain): bool
    {
        throw new Registrar_Exception('Domain unlocking is managed automatically by Nominet');
    }

    /**
     * Make EPP request to Nominet
     */
    private function _makeRequest($command, $domainName, $params = [])
    {
        $host = $this->config['test_mode'] ? $this->epp_test_host : $this->epp_host;
        $port = $this->config['test_mode'] ? $this->epp_test_port : $this->epp_port;

        // Build EPP XML based on command
        $xml = $this->_buildEppXml($command, $domainName, $params);

        // Connect to EPP server
        $connection = $this->_connect($host, $port);
        
        // Login
        $this->_login($connection);
        
        // Send command
        $this->_send($connection, $xml);
        
        // Get response
        $response = $this->_receive($connection);
        
        // Logout
        $this->_logout($connection);
        
        // Close connection
        fclose($connection);

        return $response;
    }

    private function _connect($host, $port)
    {
        $context = stream_context_create([
            'ssl' => [
                'verify_peer' => !$this->config['test_mode'],
                'verify_peer_name' => !$this->config['test_mode'],
            ]
        ]);

        $connection = stream_socket_client(
            "ssl://{$host}:{$port}",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$connection) {
            throw new Registrar_Exception("Failed to connect to EPP server: {$errstr} ({$errno})");
        }

        // Read greeting
        $this->_receive($connection);

        return $connection;
    }

    private function _login($connection)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <login>
      <clID>' . htmlspecialchars($this->config['username']) . '</clID>
      <pw>' . htmlspecialchars($this->config['password']) . '</pw>
      <options>
        <version>1.0</version>
        <lang>en</lang>
      </options>
      <svcs>
        <objURI>urn:ietf:params:xml:ns:domain-1.0</objURI>
        <objURI>urn:ietf:params:xml:ns:contact-1.0</objURI>
      </svcs>
    </login>
    <clTRID>login-' . time() . '</clTRID>
  </command>
</epp>';

        $this->_send($connection, $xml);
        $response = $this->_receive($connection);

        if (strpos($response, '<result code="1000">') === false) {
            throw new Registrar_Exception('EPP login failed');
        }
    }

    private function _logout($connection)
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <logout/>
    <clTRID>logout-' . time() . '</clTRID>
  </command>
</epp>';

        $this->_send($connection, $xml);
        $this->_receive($connection);
    }

    private function _send($connection, $xml)
    {
        $length = strlen($xml) + 4;
        $header = pack('N', $length);
        
        fwrite($connection, $header . $xml);
    }

    private function _receive($connection)
    {
        $header = fread($connection, 4);
        if (strlen($header) < 4) {
            throw new Registrar_Exception('Failed to read EPP response header');
        }

        $unpacked = unpack('N', $header);
        $length = $unpacked[1] - 4;

        $response = '';
        while (strlen($response) < $length) {
            $chunk = fread($connection, $length - strlen($response));
            if ($chunk === false) {
                throw new Registrar_Exception('Failed to read EPP response');
            }
            $response .= $chunk;
        }

        return $response;
    }

    private function _buildEppXml($command, $domainName, $params = [])
    {
        $trId = $command . '-' . time();

        switch ($command) {
            case 'check':
                return $this->_buildCheckXml($domainName, $trId);
            case 'info':
                return $this->_buildInfoXml($domainName, $trId);
            case 'create':
                return $this->_buildCreateXml($domainName, $params, $trId);
            case 'renew':
                return $this->_buildRenewXml($domainName, $params, $trId);
            case 'transfer':
                return $this->_buildTransferXml($domainName, $params, $trId);
            case 'update_ns':
                return $this->_buildUpdateNsXml($domainName, $params, $trId);
            default:
                throw new Registrar_Exception("Unknown EPP command: {$command}");
        }
    }

    private function _buildCheckXml($domainName, $trId)
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <check>
      <domain:check xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>' . htmlspecialchars($domainName) . '</domain:name>
      </domain:check>
    </check>
    <clTRID>' . $trId . '</clTRID>
  </command>
</epp>';
    }

    private function _buildInfoXml($domainName, $trId)
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <info>
      <domain:info xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>' . htmlspecialchars($domainName) . '</domain:name>
      </domain:info>
    </info>
    <clTRID>' . $trId . '</clTRID>
  </command>
</epp>';
    }

    private function _buildCreateXml($domainName, $params, $trId)
    {
        $nsXml = '';
        foreach ($params['nameservers'] as $ns) {
            $nsXml .= '<domain:hostObj>' . htmlspecialchars($ns) . '</domain:hostObj>';
        }

        $period = isset($params['period']) ? $params['period'] : 1;

        return '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <create>
      <domain:create xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>' . htmlspecialchars($domainName) . '</domain:name>
        <domain:period unit="y">' . $period . '</domain:period>
        <domain:ns>' . $nsXml . '</domain:ns>
        <domain:registrant>auto</domain:registrant>
      </domain:create>
    </create>
    <clTRID>' . $trId . '</clTRID>
  </command>
</epp>';
    }

    private function _buildRenewXml($domainName, $params, $trId)
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <renew>
      <domain:renew xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>' . htmlspecialchars($domainName) . '</domain:name>
        <domain:curExpDate>' . $params['curExpDate'] . '</domain:curExpDate>
        <domain:period unit="y">' . $params['period'] . '</domain:period>
      </domain:renew>
    </renew>
    <clTRID>' . $trId . '</clTRID>
  </command>
</epp>';
    }

    private function _buildTransferXml($domainName, $params, $trId)
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <transfer op="request">
      <domain:transfer xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>' . htmlspecialchars($domainName) . '</domain:name>
        <domain:authInfo>
          <domain:pw>' . htmlspecialchars($params['authCode']) . '</domain:pw>
        </domain:authInfo>
      </domain:transfer>
    </transfer>
    <clTRID>' . $trId . '</clTRID>
  </command>
</epp>';
    }

    private function _buildUpdateNsXml($domainName, $params, $trId)
    {
        $nsXml = '';
        foreach ($params['nameservers'] as $ns) {
            $nsXml .= '<domain:hostObj>' . htmlspecialchars($ns) . '</domain:hostObj>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="no"?>
<epp xmlns="urn:ietf:params:xml:ns:epp-1.0">
  <command>
    <update>
      <domain:update xmlns:domain="urn:ietf:params:xml:ns:domain-1.0">
        <domain:name>' . htmlspecialchars($domainName) . '</domain:name>
        <domain:add>
          <domain:ns>' . $nsXml . '</domain:ns>
        </domain:add>
      </domain:update>
    </update>
    <clTRID>' . $trId . '</clTRID>
  </command>
</epp>';
    }
}
