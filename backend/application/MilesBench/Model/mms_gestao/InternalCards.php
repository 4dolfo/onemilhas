<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * InternalCards
 *
 * @ORM\Table(name="internal_cards", indexes={@ORM\Index(name="provider_id", columns={"provider_id"})})
 * @ORM\Entity
 */
class InternalCards
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
     * @ORM\Column(name="card_number", type="string", length=60, nullable=false)
     */
    private $cardNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="card_password", type="string", length=250, nullable=false)
     */
    private $cardPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="card_type", type="string", length=20, nullable=false)
     */
    private $cardType;

    /**
     * @var string
     *
     * @ORM\Column(name="priority_airline", type="string", length=11, nullable=true)
     */
    private $priorityAirline;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false)
     */
    private $status = 'Aprovado';

    /**
     * @var string
     *
     * @ORM\Column(name="card_limit", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $cardLimit = '0.00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="due_date", type="datetime", nullable=false)
     */
    private $dueDate;

    /**
     * @var string
     *
     * @ORM\Column(name="card_used", type="decimal", precision=20, scale=0, nullable=false)
     */
    private $cardUsed = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="show_name", type="string", length=50, nullable=false)
     */
    private $showName = '';

    /**
     * @var string
     *
     * @ORM\Column(name="show_adress", type="string", length=90, nullable=false)
     */
    private $showAdress = '';

    /**
     * @var string
     *
     * @ORM\Column(name="show_registration", type="string", length=90, nullable=false)
     */
    private $showRegistration = '';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="show_birthdate", type="datetime", nullable=true)
     */
    private $showBirthdate;

    /**
     * @var integer
     *
     * @ORM\Column(name="exclusive_partner_id", type="integer", nullable=true)
     */
    private $exclusivePartnerId;

    /**
     * @var string
     *
     * @ORM\Column(name="provider_adress", type="string", length=100, nullable=false)
     */
    private $providerAdress = '';

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="provider_id", referencedColumnName="id")
     * })
     */
    private $provider;


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
     * Set cardNumber
     *
     * @param string $cardNumber
     * @return InternalCards
     */
    public function setCardNumber($cardNumber)
    {
        $this->cardNumber = $cardNumber;

        return $this;
    }

    /**
     * Get cardNumber
     *
     * @return string 
     */
    public function getCardNumber()
    {
        return $this->cardNumber;
    }

    /**
     * Set cardPassword
     *
     * @param string $cardPassword
     * @return InternalCards
     */
    public function setCardPassword($cardPassword)
    {
        $this->cardPassword = $cardPassword;

        return $this;
    }

    /**
     * Get cardPassword
     *
     * @return string 
     */
    public function getCardPassword()
    {
        return $this->cardPassword;
    }

    /**
     * Set cardType
     *
     * @param string $cardType
     * @return InternalCards
     */
    public function setCardType($cardType)
    {
        $this->cardType = $cardType;

        return $this;
    }

    /**
     * Get cardType
     *
     * @return string 
     */
    public function getCardType()
    {
        return $this->cardType;
    }

    /**
     * Set priorityAirline
     *
     * @param string $priorityAirline
     * @return InternalCards
     */
    public function setPriorityAirline($priorityAirline)
    {
        $this->priorityAirline = $priorityAirline;

        return $this;
    }

    /**
     * Get priorityAirline
     *
     * @return string 
     */
    public function getPriorityAirline()
    {
        return $this->priorityAirline;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return InternalCards
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set cardLimit
     *
     * @param string $cardLimit
     * @return InternalCards
     */
    public function setCardLimit($cardLimit)
    {
        $this->cardLimit = $cardLimit;

        return $this;
    }

    /**
     * Get cardLimit
     *
     * @return string 
     */
    public function getCardLimit()
    {
        return $this->cardLimit;
    }

    /**
     * Set dueDate
     *
     * @param \DateTime $dueDate
     * @return InternalCards
     */
    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    /**
     * Get dueDate
     *
     * @return \DateTime 
     */
    public function getDueDate()
    {
        return $this->dueDate;
    }

    /**
     * Set cardUsed
     *
     * @param string $cardUsed
     * @return InternalCards
     */
    public function setCardUsed($cardUsed)
    {
        $this->cardUsed = $cardUsed;

        return $this;
    }

    /**
     * Get cardUsed
     *
     * @return string 
     */
    public function getCardUsed()
    {
        return $this->cardUsed;
    }

    /**
     * Set showName
     *
     * @param string $showName
     * @return InternalCards
     */
    public function setShowName($showName)
    {
        $this->showName = $showName;

        return $this;
    }

    /**
     * Get showName
     *
     * @return string 
     */
    public function getShowName()
    {
        return $this->showName;
    }

    /**
     * Set showAdress
     *
     * @param string $showAdress
     * @return InternalCards
     */
    public function setShowAdress($showAdress)
    {
        $this->showAdress = $showAdress;

        return $this;
    }

    /**
     * Get showAdress
     *
     * @return string 
     */
    public function getShowAdress()
    {
        return $this->showAdress;
    }

    /**
     * Set showRegistration
     *
     * @param string $showRegistration
     * @return InternalCards
     */
    public function setShowRegistration($showRegistration)
    {
        $this->showRegistration = $showRegistration;

        return $this;
    }

    /**
     * Get showRegistration
     *
     * @return string 
     */
    public function getShowRegistration()
    {
        return $this->showRegistration;
    }

    /**
     * Set showBirthdate
     *
     * @param \DateTime $showBirthdate
     * @return InternalCards
     */
    public function setShowBirthdate($showBirthdate)
    {
        $this->showBirthdate = $showBirthdate;

        return $this;
    }

    /**
     * Get showBirthdate
     *
     * @return \DateTime 
     */
    public function getShowBirthdate()
    {
        return $this->showBirthdate;
    }

    /**
     * Set exclusivePartnerId
     *
     * @param integer $exclusivePartnerId
     * @return InternalCards
     */
    public function setExclusivePartnerId($exclusivePartnerId)
    {
        $this->exclusivePartnerId = $exclusivePartnerId;

        return $this;
    }

    /**
     * Get exclusivePartnerId
     *
     * @return integer 
     */
    public function getExclusivePartnerId()
    {
        return $this->exclusivePartnerId;
    }

    /**
     * Set providerAdress
     *
     * @param string $providerAdress
     * @return InternalCards
     */
    public function setProviderAdress($providerAdress)
    {
        $this->providerAdress = $providerAdress;

        return $this;
    }

    /**
     * Get providerAdress
     *
     * @return string 
     */
    public function getProviderAdress()
    {
        return $this->providerAdress;
    }

    /**
     * Set provider
     *
     * @param \Businesspartner $provider
     * @return InternalCards
     */
    public function setProvider(\Businesspartner $provider = null)
    {
        $this->provider = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @return \Businesspartner 
     */
    public function getProvider()
    {
        return $this->provider;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="provider_email", type="string", length=255, nullable=true)
     */
    private $providerEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     */
    private $phone;


    /**
     * Set providerEmail
     *
     * @param string $providerEmail
     * @return InternalCards
     */
    public function setProviderEmail($providerEmail)
    {
        $this->providerEmail = $providerEmail;

        return $this;
    }

    /**
     * Get providerEmail
     *
     * @return string 
     */
    public function getProviderEmail()
    {
        return $this->providerEmail;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return InternalCards
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }
}
