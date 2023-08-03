<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Airline
 *
 * @ORM\Table(name="airline", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_airline_businesspartner", columns={"provider_id"}), @ORM\Index(name="robot_cards_id", columns={"robot_cards_id"})})
 * @ORM\Entity
 */
class Airline
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
     * @ORM\Column(name="name", type="string", length=45, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="cancel_cost", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $cancelCost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="cards_limit", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $cardsLimit = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="miles_limit", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $milesLimit = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="robot_status", type="string", length=5, nullable=false)
     */
    private $robotStatus = 'false';

    /**
     * @var float
     *
     * @ORM\Column(name="baggage", type="float", precision=20, scale=2, nullable=true)
     */
    private $baggage = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="baggage_International", type="float", precision=20, scale=2, nullable=true)
     */
    private $baggageInternational = '0.00';

    /**
     * @var boolean
     *
     * @ORM\Column(name="sale_plans_status", type="boolean", nullable=true)
     */
    private $salePlansStatus;

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
     * @var \Cards
     *
     * @ORM\ManyToOne(targetEntity="Cards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="robot_cards_id", referencedColumnName="id")
     * })
     */
    private $robotCards;


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
     * Set name
     *
     * @param string $name
     * @return Airline
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set cancelCost
     *
     * @param string $cancelCost
     * @return Airline
     */
    public function setCancelCost($cancelCost)
    {
        $this->cancelCost = $cancelCost;

        return $this;
    }

    /**
     * Get cancelCost
     *
     * @return string 
     */
    public function getCancelCost()
    {
        return $this->cancelCost;
    }

    /**
     * Set cardsLimit
     *
     * @param string $cardsLimit
     * @return Airline
     */
    public function setCardsLimit($cardsLimit)
    {
        $this->cardsLimit = $cardsLimit;

        return $this;
    }

    /**
     * Get cardsLimit
     *
     * @return string 
     */
    public function getCardsLimit()
    {
        return $this->cardsLimit;
    }

    /**
     * Set milesLimit
     *
     * @param string $milesLimit
     * @return Airline
     */
    public function setMilesLimit($milesLimit)
    {
        $this->milesLimit = $milesLimit;

        return $this;
    }

    /**
     * Get milesLimit
     *
     * @return string 
     */
    public function getMilesLimit()
    {
        return $this->milesLimit;
    }

    /**
     * Set robotStatus
     *
     * @param string $robotStatus
     * @return Airline
     */
    public function setRobotStatus($robotStatus)
    {
        $this->robotStatus = $robotStatus;

        return $this;
    }

    /**
     * Get robotStatus
     *
     * @return string 
     */
    public function getRobotStatus()
    {
        return $this->robotStatus;
    }

    /**
     * Set baggage
     *
     * @param float $baggage
     * @return Airline
     */
    public function setBaggage($baggage)
    {
        $this->baggage = $baggage;

        return $this;
    }

    /**
     * Get baggage
     *
     * @return float 
     */
    public function getBaggage()
    {
        return $this->baggage;
    }

    /**
     * Set baggageInternational
     *
     * @param float $baggageInternational
     * @return Airline
     */
    public function setBaggageInternational($baggageInternational)
    {
        $this->baggageInternational = $baggageInternational;

        return $this;
    }

    /**
     * Get baggageInternational
     *
     * @return float 
     */
    public function getBaggageInternational()
    {
        return $this->baggageInternational;
    }

    /**
     * Set salePlansStatus
     *
     * @param boolean $salePlansStatus
     * @return Airline
     */
    public function setSalePlansStatus($salePlansStatus)
    {
        $this->salePlansStatus = $salePlansStatus;

        return $this;
    }

    /**
     * Get salePlansStatus
     *
     * @return boolean 
     */
    public function getSalePlansStatus()
    {
        return $this->salePlansStatus;
    }

    /**
     * Set provider
     *
     * @param \Businesspartner $provider
     * @return Airline
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
     * Set robotCards
     *
     * @param \Cards $robotCards
     * @return Airline
     */
    public function setRobotCards(\Cards $robotCards = null)
    {
        $this->robotCards = $robotCards;

        return $this;
    }

    /**
     * Get robotCards
     *
     * @return \Cards 
     */
    public function getRobotCards()
    {
        return $this->robotCards;
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
     * @return Airline
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
     * @var string
     *
     * @ORM\Column(name="max_pax_field", type="string", length=40, nullable=true)
     */
    private $maxPaxField;


    /**
     * Set maxPaxField
     *
     * @param string $maxPaxField
     * @return Airline
     */
    public function setMaxPaxField($maxPaxField)
    {
        $this->maxPaxField = $maxPaxField;

        return $this;
    }

    /**
     * Get maxPaxField
     *
     * @return string 
     */
    public function getMaxPaxField()
    {
        return $this->maxPaxField;
    }
}
