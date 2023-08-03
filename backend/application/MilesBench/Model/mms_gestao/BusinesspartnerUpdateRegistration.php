<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * BusinesspartnerUpdateRegistration
 *
 * @ORM\Table(name="businesspartner_update_registration", indexes={@ORM\Index(name="businesspartner_id", columns={"businesspartner_id"})})
 * @ORM\Entity
 */
class BusinesspartnerUpdateRegistration
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="client_name", type="string", length=40, nullable=true)
     */
    private $clientName;

    /**
     * @var string
     *
     * @ORM\Column(name="social_name", type="string", length=40, nullable=true)
     */
    private $socialName;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_code", type="string", length=40, nullable=true)
     */
    private $registrationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="adress", type="string", length=40, nullable=true)
     */
    private $adress;

    /**
     * @var string
     *
     * @ORM\Column(name="adress_number", type="string", length=40, nullable=true)
     */
    private $adressNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="adress_complement", type="string", length=40, nullable=true)
     */
    private $adressComplement;

    /**
     * @var string
     *
     * @ORM\Column(name="adress_district", type="string", length=40, nullable=true)
     */
    private $adressDistrict;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", length=40, nullable=true)
     */
    private $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=40, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_cel", type="string", length=40, nullable=true)
     */
    private $phoneCel;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_commercial", type="string", length=40, nullable=true)
     */
    private $phoneCommercial;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_residential", type="string", length=40, nullable=true)
     */
    private $phoneResidential;

    /**
     * @var string
     *
     * @ORM\Column(name="contact", type="string", length=40, nullable=true)
     */
    private $contact;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="businesspartner_id", referencedColumnName="id")
     * })
     */
    private $businesspartner;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set clientName
     *
     * @param string $clientName
     * @return BusinesspartnerUpdateRegistration
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * Get clientName
     *
     * @return string 
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * Set socialName
     *
     * @param string $socialName
     * @return BusinesspartnerUpdateRegistration
     */
    public function setSocialName($socialName)
    {
        $this->socialName = $socialName;

        return $this;
    }

    /**
     * Get socialName
     *
     * @return string 
     */
    public function getSocialName()
    {
        return $this->socialName;
    }

    /**
     * Set registrationCode
     *
     * @param string $registrationCode
     * @return BusinesspartnerUpdateRegistration
     */
    public function setRegistrationCode($registrationCode)
    {
        $this->registrationCode = $registrationCode;

        return $this;
    }

    /**
     * Get registrationCode
     *
     * @return string 
     */
    public function getRegistrationCode()
    {
        return $this->registrationCode;
    }

    /**
     * Set adress
     *
     * @param string $adress
     * @return BusinesspartnerUpdateRegistration
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;

        return $this;
    }

    /**
     * Get adress
     *
     * @return string 
     */
    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * Set adressNumber
     *
     * @param string $adressNumber
     * @return BusinesspartnerUpdateRegistration
     */
    public function setAdressNumber($adressNumber)
    {
        $this->adressNumber = $adressNumber;

        return $this;
    }

    /**
     * Get adressNumber
     *
     * @return string 
     */
    public function getAdressNumber()
    {
        return $this->adressNumber;
    }

    /**
     * Set adressComplement
     *
     * @param string $adressComplement
     * @return BusinesspartnerUpdateRegistration
     */
    public function setAdressComplement($adressComplement)
    {
        $this->adressComplement = $adressComplement;

        return $this;
    }

    /**
     * Get adressComplement
     *
     * @return string 
     */
    public function getAdressComplement()
    {
        return $this->adressComplement;
    }

    /**
     * Set adressDistrict
     *
     * @param string $adressDistrict
     * @return BusinesspartnerUpdateRegistration
     */
    public function setAdressDistrict($adressDistrict)
    {
        $this->adressDistrict = $adressDistrict;

        return $this;
    }

    /**
     * Get adressDistrict
     *
     * @return string 
     */
    public function getAdressDistrict()
    {
        return $this->adressDistrict;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     * @return BusinesspartnerUpdateRegistration
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string 
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return BusinesspartnerUpdateRegistration
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phoneCel
     *
     * @param string $phoneCel
     * @return BusinesspartnerUpdateRegistration
     */
    public function setPhoneCel($phoneCel)
    {
        $this->phoneCel = $phoneCel;

        return $this;
    }

    /**
     * Get phoneCel
     *
     * @return string 
     */
    public function getPhoneCel()
    {
        return $this->phoneCel;
    }

    /**
     * Set phoneCommercial
     *
     * @param string $phoneCommercial
     * @return BusinesspartnerUpdateRegistration
     */
    public function setPhoneCommercial($phoneCommercial)
    {
        $this->phoneCommercial = $phoneCommercial;

        return $this;
    }

    /**
     * Get phoneCommercial
     *
     * @return string 
     */
    public function getPhoneCommercial()
    {
        return $this->phoneCommercial;
    }

    /**
     * Set phoneResidential
     *
     * @param string $phoneResidential
     * @return BusinesspartnerUpdateRegistration
     */
    public function setPhoneResidential($phoneResidential)
    {
        $this->phoneResidential = $phoneResidential;

        return $this;
    }

    /**
     * Get phoneResidential
     *
     * @return string 
     */
    public function getPhoneResidential()
    {
        return $this->phoneResidential;
    }

    /**
     * Set contact
     *
     * @param string $contact
     * @return BusinesspartnerUpdateRegistration
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set businesspartner
     *
     * @param \Businesspartner $businesspartner
     * @return BusinesspartnerUpdateRegistration
     */
    public function setBusinesspartner(\Businesspartner $businesspartner = null)
    {
        $this->businesspartner = $businesspartner;

        return $this;
    }

    /**
     * Get businesspartner
     *
     * @return \Businesspartner 
     */
    public function getBusinesspartner()
    {
        return $this->businesspartner;
    }
}
