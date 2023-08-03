<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Cards
 *
 * @ORM\Table(name="cards", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_Cards_airline1_idx", columns={"airline_id"}), @ORM\Index(name="fk_Cards_businesspartner1_idx", columns={"businesspartner_id"}), @ORM\Index(name="card_tax", columns={"card_tax"})})
 * @ORM\Entity
 */
class Cards
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
     * @ORM\Column(name="card_number", type="string", length=45, nullable=false)
     */
    private $cardNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="access_password", type="string", length=512, nullable=true)
     */
    private $accessPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="access_id", type="string", length=45, nullable=true)
     */
    private $accessId;

    /**
     * @var string
     *
     * @ORM\Column(name="recovery_password", type="string", length=512, nullable=true)
     */
    private $recoveryPassword;

    /**
     * @var string
     *
     * @ORM\Column(name="blocked", type="string", length=1, nullable=false)
     */
    private $blocked = 'N';

    /**
     * @var string
     *
     * @ORM\Column(name="card_type", type="string", length=14, nullable=true)
     */
    private $cardType;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=20, nullable=true)
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="is_priority", type="string", length=5, nullable=true)
     */
    private $isPriority = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="user_session", type="string", length=150, nullable=true)
     */
    private $userSession = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="days_priority", type="integer", nullable=true)
     */
    private $daysPriority;

    /**
     * @var \Airline
     *
     * @ORM\ManyToOne(targetEntity="Airline")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="airline_id", referencedColumnName="id")
     * })
     */
    private $airline;

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
     * @var \InternalCards
     *
     * @ORM\ManyToOne(targetEntity="InternalCards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="card_tax", referencedColumnName="id")
     * })
     */
    private $cardTax;

    /**
     * One Cards has many sales.
     * @ORM\OneToMany(targetEntity="Sale", mappedBy="cards")
     */
    private $sales;

    public function __construct() {
        $this->sales = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getSales() {
        return $this->sales;
    }

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
     * @return Cards
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
     * Set accessPassword
     *
     * @param string $accessPassword
     * @return Cards
     */
    public function setAccessPassword($accessPassword)
    {
        $this->accessPassword = $accessPassword;

        return $this;
    }

    /**
     * Get accessPassword
     *
     * @return string 
     */
    public function getAccessPassword()
    {
        return $this->accessPassword;
    }

    /**
     * Set accessId
     *
     * @param string $accessId
     * @return Cards
     */
    public function setAccessId($accessId)
    {
        $this->accessId = $accessId;

        return $this;
    }

    /**
     * Get accessId
     *
     * @return string 
     */
    public function getAccessId()
    {
        return $this->accessId;
    }

    /**
     * Set recoveryPassword
     *
     * @param string $recoveryPassword
     * @return Cards
     */
    public function setRecoveryPassword($recoveryPassword)
    {
        $this->recoveryPassword = $recoveryPassword;

        return $this;
    }

    /**
     * Get recoveryPassword
     *
     * @return string 
     */
    public function getRecoveryPassword()
    {
        return $this->recoveryPassword;
    }

    /**
     * Set blocked
     *
     * @param string $blocked
     * @return Cards
     */
    public function setBlocked($blocked)
    {
        $this->blocked = $blocked;

        return $this;
    }

    /**
     * Get blocked
     *
     * @return string 
     */
    public function getBlocked()
    {
        return $this->blocked;
    }

    /**
     * Set cardType
     *
     * @param string $cardType
     * @return Cards
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
     * Set token
     *
     * @param string $token
     * @return Cards
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set isPriority
     *
     * @param string $isPriority
     * @return Cards
     */
    public function setIsPriority($isPriority)
    {
        $this->isPriority = $isPriority;

        return $this;
    }

    /**
     * Get isPriority
     *
     * @return string 
     */
    public function getIsPriority()
    {
        return $this->isPriority;
    }

    /**
     * Set userSession
     *
     * @param string $userSession
     * @return Cards
     */
    public function setUserSession($userSession)
    {
        $this->userSession = $userSession;

        return $this;
    }

    /**
     * Get userSession
     *
     * @return string 
     */
    public function getUserSession()
    {
        return $this->userSession;
    }

    /**
     * Set daysPriority
     *
     * @param integer $daysPriority
     * @return Cards
     */
    public function setDaysPriority($daysPriority)
    {
        $this->daysPriority = $daysPriority;

        return $this;
    }

    /**
     * Get daysPriority
     *
     * @return integer 
     */
    public function getDaysPriority()
    {
        return $this->daysPriority;
    }

    /**
     * Set airline
     *
     * @param \Airline $airline
     * @return Cards
     */
    public function setAirline(\Airline $airline = null)
    {
        $this->airline = $airline;

        return $this;
    }

    /**
     * Get airline
     *
     * @return \Airline 
     */
    public function getAirline()
    {
        return $this->airline;
    }

    /**
     * Set businesspartner
     *
     * @param \Businesspartner $businesspartner
     * @return Cards
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

    /**
     * Set cardTax
     *
     * @param \InternalCards $cardTax
     * @return Cards
     */
    public function setCardTax(\InternalCards $cardTax = null)
    {
        $this->cardTax = $cardTax;

        return $this;
    }

    /**
     * Get cardTax
     *
     * @return \InternalCards 
     */
    public function getCardTax()
    {
        return $this->cardTax;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="string", length=512, nullable=true)
     */
    private $notes;


    /**
     * Set notes
     *
     * @param string $notes
     * @return Cards
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string 
     */
    public function getNotes()
    {
        return $this->notes;
    }
    /**
     * @var integer
     *
     * @ORM\Column(name="people_used_by_the_card", type="integer", nullable=true)
     */
    private $peopleUsedByTheCard = '0';


    /**
     * Set peopleUsedByTheCard
     *
     * @param integer $peopleUsedByTheCard
     * @return Cards
     */
    public function setPeopleUsedByTheCard($peopleUsedByTheCard)
    {
        $this->peopleUsedByTheCard = $peopleUsedByTheCard;

        return $this;
    }

    /**
     * Get peopleUsedByTheCard
     *
     * @return integer 
     */
    public function getPeopleUsedByTheCard()
    {
        return $this->peopleUsedByTheCard;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="max_per_pax", type="float", precision=20, scale=2, nullable=true)
     */
    private $maxPerPax = '0.00';


    /**
     * Set maxPerPax
     *
     * @param float $maxPerPax
     * @return Cards
     */
    public function setMaxPerPax($maxPerPax)
    {
        $this->maxPerPax = $maxPerPax;

        return $this;
    }

    /**
     * Get maxPerPax
     *
     * @return float 
     */
    public function getMaxPerPax()
    {
        return $this->maxPerPax;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="minimum_miles", type="float", precision=20, scale=2, nullable=true)
     */
    private $minimumMiles = '0.00';


    /**
     * Set minimumMiles
     *
     * @param float $minimumMiles
     * @return Cards
     */
    public function setMinimumMiles($minimumMiles)
    {
        $this->minimumMiles = $minimumMiles;

        return $this;
    }

    /**
     * Get minimumMiles
     *
     * @return float 
     */
    public function getMinimumMiles()
    {
        return $this->minimumMiles;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="only_inter", type="string", length=5, nullable=true)
     */
    private $onlyInter;


    /**
     * Set onlyInter
     *
     * @param string $onlyInter
     * @return Cards
     */
    public function setOnlyInter($onlyInter)
    {
        $this->onlyInter = $onlyInter;

        return $this;
    }

    /**
     * Get onlyInter
     *
     * @return string 
     */
    public function getOnlyInter()
    {
        return $this->onlyInter;
    }
    /**
     * @var integer
     *
     * @ORM\Column(name="max_diamond_pax", type="integer", nullable=true)
     */
    private $maxDiamondPax = '0';


    /**
     * Set maxDiamondPax
     *
     * @param integer $maxDiamondPax
     * @return Cards
     */
    public function setMaxDiamondPax($maxDiamondPax)
    {
        $this->maxDiamondPax = $maxDiamondPax;

        return $this;
    }

    /**
     * Get maxDiamondPax
     *
     * @return integer 
     */
    public function getMaxDiamondPax()
    {
        return $this->maxDiamondPax;
    }

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="user_session_date", type="datetime", nullable=true)
     */
    private $userSessionDate;

    /**
     * Set userSessionDate
     *
     * @param \DateTime $userSessionDate
     * @return Cards
     */
    public function setUserSessionDate($userSessionDate)
    {
        $this->userSessionDate = $userSessionDate;

        return $this;
    }

    /**
     * Get userSessionDate
     *
     * @return \DateTime 
     */
    public function getUserSessionDate()
    {
        return $this->userSessionDate;
    }
}
