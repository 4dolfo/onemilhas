<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * MilesbenchCategory
 *
 * @ORM\Table(name="milesbench_category", indexes={@ORM\Index(name="cards_id", columns={"cards_id"}), @ORM\Index(name="flight_category", columns={"flight_category"})})
 * @ORM\Entity
 */
class MilesbenchCategory
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
     * @var float
     *
     * @ORM\Column(name="percentage", type="float", precision=20, scale=2, nullable=false)
     */
    private $percentage = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="days", type="integer", nullable=false)
     */
    private $days;

    /**
     * @var float
     *
     * @ORM\Column(name="to_free", type="float", precision=20, scale=2, nullable=false)
     */
    private $toFree = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="used", type="float", precision=20, scale=2, nullable=false)
     */
    private $used = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="original_to_free", type="float", precision=20, scale=2, nullable=false)
     */
    private $originalToFree = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="to_negative", type="float", precision=20, scale=2, nullable=true)
     */
    private $toNegative = '0.00';

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
     * @var \AzulFlightCategory
     *
     * @ORM\ManyToOne(targetEntity="AzulFlightCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="flight_category", referencedColumnName="id")
     * })
     */
    private $flightCategory;


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
     * Set percentage
     *
     * @param float $percentage
     * @return MilesbenchCategory
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;

        return $this;
    }

    /**
     * Get percentage
     *
     * @return float 
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * Set days
     *
     * @param integer $days
     * @return MilesbenchCategory
     */
    public function setDays($days)
    {
        $this->days = $days;

        return $this;
    }

    /**
     * Get days
     *
     * @return integer 
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * Set toFree
     *
     * @param float $toFree
     * @return MilesbenchCategory
     */
    public function setToFree($toFree)
    {
        $this->toFree = $toFree;

        return $this;
    }

    /**
     * Get toFree
     *
     * @return float 
     */
    public function getToFree()
    {
        return $this->toFree;
    }

    /**
     * Set used
     *
     * @param float $used
     * @return MilesbenchCategory
     */
    public function setUsed($used)
    {
        $this->used = $used;

        return $this;
    }

    /**
     * Get used
     *
     * @return float 
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Set originalToFree
     *
     * @param float $originalToFree
     * @return MilesbenchCategory
     */
    public function setOriginalToFree($originalToFree)
    {
        $this->originalToFree = $originalToFree;

        return $this;
    }

    /**
     * Get originalToFree
     *
     * @return float 
     */
    public function getOriginalToFree()
    {
        return $this->originalToFree;
    }

    /**
     * Set toNegative
     *
     * @param float $toNegative
     * @return MilesbenchCategory
     */
    public function setToNegative($toNegative)
    {
        $this->toNegative = $toNegative;

        return $this;
    }

    /**
     * Get toNegative
     *
     * @return float 
     */
    public function getToNegative()
    {
        return $this->toNegative;
    }

    /**
     * Set cards
     *
     * @param \Cards $cards
     * @return MilesbenchCategory
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
     * Set flightCategory
     *
     * @param \AzulFlightCategory $flightCategory
     * @return MilesbenchCategory
     */
    public function setFlightCategory(\AzulFlightCategory $flightCategory = null)
    {
        $this->flightCategory = $flightCategory;

        return $this;
    }

    /**
     * Get flightCategory
     *
     * @return \AzulFlightCategory 
     */
    public function getFlightCategory()
    {
        return $this->flightCategory;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="control", type="string", length=3, nullable=true)
     */
    private $control;


    /**
     * Set control
     *
     * @param string $control
     * @return MilesbenchCategory
     */
    public function setControl($control)
    {
        $this->control = $control;

        return $this;
    }

    /**
     * Get control
     *
     * @return string 
     */
    public function getControl()
    {
        return $this->control;
    }
}
