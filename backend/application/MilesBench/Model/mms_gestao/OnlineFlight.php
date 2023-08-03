<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * OnlineFlight
 *
 * @ORM\Table(name="online_flight", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_online_flight_order", columns={"order_id"}), @ORM\Index(name="cards_id", columns={"cards_id"}), @ORM\Index(name="provider", columns={"provider"})})
 * @ORM\Entity
 */
class OnlineFlight
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
     * @ORM\Column(name="airline", type="string", length=45, nullable=false)
     */
    private $airline;

    /**
     * @var string
     *
     * @ORM\Column(name="flight", type="string", length=45, nullable=false)
     */
    private $flight;

    /**
     * @var string
     *
     * @ORM\Column(name="connection", type="string", length=200, nullable=true)
     */
    private $connection;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="boarding_date", type="datetime", nullable=false)
     */
    private $boardingDate;

    /**
     * @var string
     *
     * @ORM\Column(name="airport_code_from", type="string", length=45, nullable=false)
     */
    private $airportCodeFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="airport_code_to", type="string", length=45, nullable=false)
     */
    private $airportCodeTo;

    /**
     * @var string
     *
     * @ORM\Column(name="airport_description_from", type="string", length=200, nullable=true)
     */
    private $airportDescriptionFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="airport_description_to", type="string", length=200, nullable=true)
     */
    private $airportDescriptionTo;

    /**
     * @var string
     *
     * @ORM\Column(name="flight_time", type="string", length=5, nullable=true)
     */
    private $flightTime;

    /**
     * @var string
     *
     * @ORM\Column(name="miles_used", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $milesUsed = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $cost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="miles_per_adult", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $milesPerAdult = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="miles_per_child", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $milesPerChild = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="miles_per_newborn", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $milesPerNewborn = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="cost_per_adult", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $costPerAdult = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="cost_per_child", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $costPerChild = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="cost_per_newborn", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $costPerNewborn = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="number_of_adult", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $numberOfAdult = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="number_of_child", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $numberOfChild = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="number_of_newborn", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $numberOfNewborn = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="tax", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $tax = '0.00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="landing_date", type="datetime", nullable=false)
     */
    private $landingDate;

    /**
     * @var string
     *
     * @ORM\Column(name="reservation_code", type="string", length=50, nullable=true)
     */
    private $reservationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="miles_money", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $milesMoney = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=100, nullable=true)
     */
    private $class;

    /**
     * @var string
     *
     * @ORM\Column(name="emission_method", type="string", length=64, nullable=true)
     */
    private $emissionMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="fare_class", type="string", length=100, nullable=true)
     */
    private $fareClass;

    /**
     * @var float
     *
     * @ORM\Column(name="baggage_price", type="float", precision=20, scale=2, nullable=false)
     */
    private $baggagePrice = '0.00';

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="provider", referencedColumnName="id")
     * })
     */
    private $provider;

    /**
     * @var \Cards
     *
     * @ORM\ManyToOne(targetEntity="Cards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cards_id", referencedColumnName="id")
     * })
     */
    private $cards;

    /**
     * @var \OnlineOrder
     *
     * @ORM\ManyToOne(targetEntity="OnlineOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * })
     */
    private $order;


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
     * Set airline
     *
     * @param string $airline
     * @return OnlineFlight
     */
    public function setAirline($airline)
    {
        $this->airline = $airline;

        return $this;
    }

    /**
     * Get airline
     *
     * @return string 
     */
    public function getAirline()
    {
        return $this->airline;
    }

    /**
     * Set flight
     *
     * @param string $flight
     * @return OnlineFlight
     */
    public function setFlight($flight)
    {
        $this->flight = $flight;

        return $this;
    }

    /**
     * Get flight
     *
     * @return string 
     */
    public function getFlight()
    {
        return $this->flight;
    }

    /**
     * Set connection
     *
     * @param string $connection
     * @return OnlineFlight
     */
    public function setConnection($connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Get connection
     *
     * @return string 
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set boardingDate
     *
     * @param \DateTime $boardingDate
     * @return OnlineFlight
     */
    public function setBoardingDate($boardingDate)
    {
        $this->boardingDate = $boardingDate;

        return $this;
    }

    /**
     * Get boardingDate
     *
     * @return \DateTime 
     */
    public function getBoardingDate()
    {
        return $this->boardingDate;
    }

    /**
     * Set airportCodeFrom
     *
     * @param string $airportCodeFrom
     * @return OnlineFlight
     */
    public function setAirportCodeFrom($airportCodeFrom)
    {
        $this->airportCodeFrom = $airportCodeFrom;

        return $this;
    }

    /**
     * Get airportCodeFrom
     *
     * @return string 
     */
    public function getAirportCodeFrom()
    {
        return $this->airportCodeFrom;
    }

    /**
     * Set airportCodeTo
     *
     * @param string $airportCodeTo
     * @return OnlineFlight
     */
    public function setAirportCodeTo($airportCodeTo)
    {
        $this->airportCodeTo = $airportCodeTo;

        return $this;
    }

    /**
     * Get airportCodeTo
     *
     * @return string 
     */
    public function getAirportCodeTo()
    {
        return $this->airportCodeTo;
    }

    /**
     * Set airportDescriptionFrom
     *
     * @param string $airportDescriptionFrom
     * @return OnlineFlight
     */
    public function setAirportDescriptionFrom($airportDescriptionFrom)
    {
        $this->airportDescriptionFrom = $airportDescriptionFrom;

        return $this;
    }

    /**
     * Get airportDescriptionFrom
     *
     * @return string 
     */
    public function getAirportDescriptionFrom()
    {
        return $this->airportDescriptionFrom;
    }

    /**
     * Set airportDescriptionTo
     *
     * @param string $airportDescriptionTo
     * @return OnlineFlight
     */
    public function setAirportDescriptionTo($airportDescriptionTo)
    {
        $this->airportDescriptionTo = $airportDescriptionTo;

        return $this;
    }

    /**
     * Get airportDescriptionTo
     *
     * @return string 
     */
    public function getAirportDescriptionTo()
    {
        return $this->airportDescriptionTo;
    }

    /**
     * Set flightTime
     *
     * @param string $flightTime
     * @return OnlineFlight
     */
    public function setFlightTime($flightTime)
    {
        $this->flightTime = $flightTime;

        return $this;
    }

    /**
     * Get flightTime
     *
     * @return string 
     */
    public function getFlightTime()
    {
        return $this->flightTime;
    }

    /**
     * Set milesUsed
     *
     * @param string $milesUsed
     * @return OnlineFlight
     */
    public function setMilesUsed($milesUsed)
    {
        $this->milesUsed = $milesUsed;

        return $this;
    }

    /**
     * Get milesUsed
     *
     * @return string 
     */
    public function getMilesUsed()
    {
        return $this->milesUsed;
    }

    /**
     * Set cost
     *
     * @param string $cost
     * @return OnlineFlight
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return string 
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set milesPerAdult
     *
     * @param string $milesPerAdult
     * @return OnlineFlight
     */
    public function setMilesPerAdult($milesPerAdult)
    {
        $this->milesPerAdult = $milesPerAdult;

        return $this;
    }

    /**
     * Get milesPerAdult
     *
     * @return string 
     */
    public function getMilesPerAdult()
    {
        return $this->milesPerAdult;
    }

    /**
     * Set milesPerChild
     *
     * @param string $milesPerChild
     * @return OnlineFlight
     */
    public function setMilesPerChild($milesPerChild)
    {
        $this->milesPerChild = $milesPerChild;

        return $this;
    }

    /**
     * Get milesPerChild
     *
     * @return string 
     */
    public function getMilesPerChild()
    {
        return $this->milesPerChild;
    }

    /**
     * Set milesPerNewborn
     *
     * @param string $milesPerNewborn
     * @return OnlineFlight
     */
    public function setMilesPerNewborn($milesPerNewborn)
    {
        $this->milesPerNewborn = $milesPerNewborn;

        return $this;
    }

    /**
     * Get milesPerNewborn
     *
     * @return string 
     */
    public function getMilesPerNewborn()
    {
        return $this->milesPerNewborn;
    }

    /**
     * Set costPerAdult
     *
     * @param string $costPerAdult
     * @return OnlineFlight
     */
    public function setCostPerAdult($costPerAdult)
    {
        $this->costPerAdult = $costPerAdult;

        return $this;
    }

    /**
     * Get costPerAdult
     *
     * @return string 
     */
    public function getCostPerAdult()
    {
        return $this->costPerAdult;
    }

    /**
     * Set costPerChild
     *
     * @param string $costPerChild
     * @return OnlineFlight
     */
    public function setCostPerChild($costPerChild)
    {
        $this->costPerChild = $costPerChild;

        return $this;
    }

    /**
     * Get costPerChild
     *
     * @return string 
     */
    public function getCostPerChild()
    {
        return $this->costPerChild;
    }

    /**
     * Set costPerNewborn
     *
     * @param string $costPerNewborn
     * @return OnlineFlight
     */
    public function setCostPerNewborn($costPerNewborn)
    {
        $this->costPerNewborn = $costPerNewborn;

        return $this;
    }

    /**
     * Get costPerNewborn
     *
     * @return string 
     */
    public function getCostPerNewborn()
    {
        return $this->costPerNewborn;
    }

    /**
     * Set numberOfAdult
     *
     * @param string $numberOfAdult
     * @return OnlineFlight
     */
    public function setNumberOfAdult($numberOfAdult)
    {
        $this->numberOfAdult = $numberOfAdult;

        return $this;
    }

    /**
     * Get numberOfAdult
     *
     * @return string 
     */
    public function getNumberOfAdult()
    {
        return $this->numberOfAdult;
    }

    /**
     * Set numberOfChild
     *
     * @param string $numberOfChild
     * @return OnlineFlight
     */
    public function setNumberOfChild($numberOfChild)
    {
        $this->numberOfChild = $numberOfChild;

        return $this;
    }

    /**
     * Get numberOfChild
     *
     * @return string 
     */
    public function getNumberOfChild()
    {
        return $this->numberOfChild;
    }

    /**
     * Set numberOfNewborn
     *
     * @param string $numberOfNewborn
     * @return OnlineFlight
     */
    public function setNumberOfNewborn($numberOfNewborn)
    {
        $this->numberOfNewborn = $numberOfNewborn;

        return $this;
    }

    /**
     * Get numberOfNewborn
     *
     * @return string 
     */
    public function getNumberOfNewborn()
    {
        return $this->numberOfNewborn;
    }

    /**
     * Set tax
     *
     * @param string $tax
     * @return OnlineFlight
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get tax
     *
     * @return string 
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set landingDate
     *
     * @param \DateTime $landingDate
     * @return OnlineFlight
     */
    public function setLandingDate($landingDate)
    {
        $this->landingDate = $landingDate;

        return $this;
    }

    /**
     * Get landingDate
     *
     * @return \DateTime 
     */
    public function getLandingDate()
    {
        return $this->landingDate;
    }

    /**
     * Set reservationCode
     *
     * @param string $reservationCode
     * @return OnlineFlight
     */
    public function setReservationCode($reservationCode)
    {
        $this->reservationCode = $reservationCode;

        return $this;
    }

    /**
     * Get reservationCode
     *
     * @return string 
     */
    public function getReservationCode()
    {
        return $this->reservationCode;
    }

    /**
     * Set milesMoney
     *
     * @param string $milesMoney
     * @return OnlineFlight
     */
    public function setMilesMoney($milesMoney)
    {
        $this->milesMoney = $milesMoney;

        return $this;
    }

    /**
     * Get milesMoney
     *
     * @return string 
     */
    public function getMilesMoney()
    {
        return $this->milesMoney;
    }

    /**
     * Set class
     *
     * @param string $class
     * @return OnlineFlight
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set emissionMethod
     *
     * @param string $emissionMethod
     * @return OnlineFlight
     */
    public function setEmissionMethod($emissionMethod)
    {
        $this->emissionMethod = $emissionMethod;

        return $this;
    }

    /**
     * Get emissionMethod
     *
     * @return string 
     */
    public function getEmissionMethod()
    {
        return $this->emissionMethod;
    }

    /**
     * Set fareClass
     *
     * @param string $fareClass
     * @return OnlineFlight
     */
    public function setFareClass($fareClass)
    {
        $this->fareClass = $fareClass;

        return $this;
    }

    /**
     * Get fareClass
     *
     * @return string 
     */
    public function getFareClass()
    {
        return $this->fareClass;
    }

    /**
     * Set baggagePrice
     *
     * @param float $baggagePrice
     * @return OnlineFlight
     */
    public function setBaggagePrice($baggagePrice)
    {
        $this->baggagePrice = $baggagePrice;

        return $this;
    }

    /**
     * Get baggagePrice
     *
     * @return float 
     */
    public function getBaggagePrice()
    {
        return $this->baggagePrice;
    }

    /**
     * Set provider
     *
     * @param \Businesspartner $provider
     * @return OnlineFlight
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
     * Set cards
     *
     * @param \Cards $cards
     * @return OnlineFlight
     */
    public function setCards(\Cards $cards = null)
    {
        $this->cards = $cards;

        return $this;
    }

    /**
     * Get cards
     *
     * @return \Cards 
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Set order
     *
     * @param \OnlineOrder $order
     * @return OnlineFlight
     */
    public function setOrder(\OnlineOrder $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \OnlineOrder 
     */
    public function getOrder()
    {
        return $this->order;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="du_tax", type="float", precision=20, scale=2, nullable=true)
     */
    private $duTax = '0.00';


    /**
     * Set duTax
     *
     * @param float $duTax
     * @return OnlineFlight
     */
    public function setDuTax($duTax)
    {
        $this->duTax = $duTax;

        return $this;
    }

    /**
     * Get duTax
     *
     * @return float 
     */
    public function getDuTax()
    {
        return $this->duTax;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="voo_id", type="string", length=255, nullable=true)
     */
    private $vooId;

    /**
     * @var string
     *
     * @ORM\Column(name="voo_offer_id", type="string", length=255, nullable=true)
     */
    private $vooOfferId;


    /**
     * Set vooId
     *
     * @param string $vooId
     * @return OnlineFlight
     */
    public function setVooId($vooId)
    {
        $this->vooId = $vooId;

        return $this;
    }

    /**
     * Get vooId
     *
     * @return string 
     */
    public function getVooId()
    {
        return $this->vooId;
    }

    /**
     * Set vooOfferId
     *
     * @param string $vooOfferId
     * @return OnlineFlight
     */
    public function setVooOfferId($vooOfferId)
    {
        $this->vooOfferId = $vooOfferId;

        return $this;
    }

    /**
     * Get vooOfferId
     *
     * @return string 
     */
    public function getVooOfferId()
    {
        return $this->vooOfferId;
    }
}
