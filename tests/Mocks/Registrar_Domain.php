<?php

/**
 * Mock Registrar_Domain for testing
 * 
 * This mock class simulates FOSSBilling's Registrar_Domain
 * for unit testing without requiring the full FOSSBilling installation.
 */
class Registrar_Domain
{
    private $name;
    private $sld;
    private $tld;
    private $ns1;
    private $ns2;
    private $ns3;
    private $ns4;
    private $registrationTime;
    private $expirationTime;
    private $epp;
    private $contactRegistrant;
    private $contactAdmin;
    private $contactTech;
    private $contactBilling;

    /**
     * Get full domain name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name ?? ($this->sld . '.' . $this->tld);
    }

    /**
     * Set full domain name
     *
     * @param string $name
     * @return self
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get second-level domain
     *
     * @return string|null
     */
    public function getSld(): ?string
    {
        return $this->sld;
    }

    /**
     * Set second-level domain
     *
     * @param string $sld
     * @return self
     */
    public function setSld(string $sld): self
    {
        $this->sld = $sld;
        return $this;
    }

    /**
     * Get top-level domain
     *
     * @return string|null
     */
    public function getTld(): ?string
    {
        return $this->tld;
    }

    /**
     * Set top-level domain
     *
     * @param string $tld
     * @return self
     */
    public function setTld(string $tld): self
    {
        $this->tld = $tld;
        return $this;
    }

    /**
     * Get nameserver 1
     *
     * @return string|null
     */
    public function getNs1(): ?string
    {
        return $this->ns1;
    }

    /**
     * Set nameserver 1
     *
     * @param string $ns1
     * @return self
     */
    public function setNs1(string $ns1): self
    {
        $this->ns1 = $ns1;
        return $this;
    }

    /**
     * Get nameserver 2
     *
     * @return string|null
     */
    public function getNs2(): ?string
    {
        return $this->ns2;
    }

    /**
     * Set nameserver 2
     *
     * @param string $ns2
     * @return self
     */
    public function setNs2(string $ns2): self
    {
        $this->ns2 = $ns2;
        return $this;
    }

    /**
     * Get nameserver 3
     *
     * @return string|null
     */
    public function getNs3(): ?string
    {
        return $this->ns3;
    }

    /**
     * Set nameserver 3
     *
     * @param string $ns3
     * @return self
     */
    public function setNs3(string $ns3): self
    {
        $this->ns3 = $ns3;
        return $this;
    }

    /**
     * Get nameserver 4
     *
     * @return string|null
     */
    public function getNs4(): ?string
    {
        return $this->ns4;
    }

    /**
     * Set nameserver 4
     *
     * @param string $ns4
     * @return self
     */
    public function setNs4(string $ns4): self
    {
        $this->ns4 = $ns4;
        return $this;
    }

    /**
     * Get registration time (Unix timestamp)
     *
     * @return int|null
     */
    public function getRegistrationTime(): ?int
    {
        return $this->registrationTime;
    }

    /**
     * Set registration time
     *
     * @param int $time
     * @return self
     */
    public function setRegistrationTime(int $time): self
    {
        $this->registrationTime = $time;
        return $this;
    }

    /**
     * Get expiration time (Unix timestamp)
     *
     * @return int|null
     */
    public function getExpirationTime(): ?int
    {
        return $this->expirationTime;
    }

    /**
     * Set expiration time
     *
     * @param int $time
     * @return self
     */
    public function setExpirationTime(int $time): self
    {
        $this->expirationTime = $time;
        return $this;
    }

    /**
     * Get EPP/auth code
     *
     * @return string|null
     */
    public function getEpp(): ?string
    {
        return $this->epp;
    }

    /**
     * Set EPP/auth code
     *
     * @param string $epp
     * @return self
     */
    public function setEpp(string $epp): self
    {
        $this->epp = $epp;
        return $this;
    }

    /**
     * Get registrant contact
     *
     * @return Registrar_Domain_Contact|null
     */
    public function getContactRegistrant(): ?Registrar_Domain_Contact
    {
        return $this->contactRegistrant;
    }

    /**
     * Set registrant contact
     *
     * @param Registrar_Domain_Contact $contact
     * @return self
     */
    public function setContactRegistrant(Registrar_Domain_Contact $contact): self
    {
        $this->contactRegistrant = $contact;
        return $this;
    }

    /**
     * Get admin contact
     *
     * @return Registrar_Domain_Contact|null
     */
    public function getContactAdmin(): ?Registrar_Domain_Contact
    {
        return $this->contactAdmin;
    }

    /**
     * Set admin contact
     *
     * @param Registrar_Domain_Contact $contact
     * @return self
     */
    public function setContactAdmin(Registrar_Domain_Contact $contact): self
    {
        $this->contactAdmin = $contact;
        return $this;
    }

    /**
     * Get tech contact
     *
     * @return Registrar_Domain_Contact|null
     */
    public function getContactTech(): ?Registrar_Domain_Contact
    {
        return $this->contactTech;
    }

    /**
     * Set tech contact
     *
     * @param Registrar_Domain_Contact $contact
     * @return self
     */
    public function setContactTech(Registrar_Domain_Contact $contact): self
    {
        $this->contactTech = $contact;
        return $this;
    }

    /**
     * Get billing contact
     *
     * @return Registrar_Domain_Contact|null
     */
    public function getContactBilling(): ?Registrar_Domain_Contact
    {
        return $this->contactBilling;
    }

    /**
     * Set billing contact
     *
     * @param Registrar_Domain_Contact $contact
     * @return self
     */
    public function setContactBilling(Registrar_Domain_Contact $contact): self
    {
        $this->contactBilling = $contact;
        return $this;
    }
}

/**
 * Mock Registrar_Domain_Contact for testing
 */
class Registrar_Domain_Contact
{
    private $firstName;
    private $lastName;
    private $email;
    private $company;
    private $address1;
    private $address2;
    private $city;
    private $state;
    private $zip;
    private $country;
    private $tel;

    public function getFirstName(): ?string { return $this->firstName; }
    public function setFirstName(string $value): self { $this->firstName = $value; return $this; }
    
    public function getLastName(): ?string { return $this->lastName; }
    public function setLastName(string $value): self { $this->lastName = $value; return $this; }
    
    public function getEmail(): ?string { return $this->email; }
    public function setEmail(string $value): self { $this->email = $value; return $this; }
    
    public function getCompany(): ?string { return $this->company; }
    public function setCompany(string $value): self { $this->company = $value; return $this; }
    
    public function getAddress1(): ?string { return $this->address1; }
    public function setAddress1(string $value): self { $this->address1 = $value; return $this; }
    
    public function getAddress2(): ?string { return $this->address2; }
    public function setAddress2(string $value): self { $this->address2 = $value; return $this; }
    
    public function getCity(): ?string { return $this->city; }
    public function setCity(string $value): self { $this->city = $value; return $this; }
    
    public function getState(): ?string { return $this->state; }
    public function setState(string $value): self { $this->state = $value; return $this; }
    
    public function getZip(): ?string { return $this->zip; }
    public function setZip(string $value): self { $this->zip = $value; return $this; }
    
    public function getCountry(): ?string { return $this->country; }
    public function setCountry(string $value): self { $this->country = $value; return $this; }
    
    public function getTel(): ?string { return $this->tel; }
    public function setTel(string $value): self { $this->tel = $value; return $this; }
}
