<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PlansControl
 *
 * @ORM\Table(name="plans_control", indexes={@ORM\Index(name="plans_control_config_id", columns={"plans_control_config_id"})})
 * @ORM\Entity
 */
class PlansControl
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
     * @var integer
     *
     * @ORM\Column(name="minimum_points", type="integer", nullable=false)
     */
    private $minimumPoints;

    /**
     * @var integer
     *
     * @ORM\Column(name="maximum_points", type="integer", nullable=false)
     */
    private $maximumPoints;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $value;

    /**
     * @var float
     *
     * @ORM\Column(name="discount_markup", type="float", precision=20, scale=2, nullable=false)
     */
    private $discountMarkup = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="days_start", type="float", precision=20, scale=2, nullable=false)
     */
    private $daysStart = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="days_end", type="float", precision=20, scale=2, nullable=false)
     */
    private $daysEnd = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="percentage", type="float", precision=20, scale=2, nullable=false)
     */
    private $percentage = '0.00';

    /**
     * @var \PlansControlConfig
     *
     * @ORM\ManyToOne(targetEntity="PlansControlConfig")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plans_control_config_id", referencedColumnName="id")
     * })
     */
    private $plansControlConfig;


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
     * Set minimumPoints
     *
     * @param integer $minimumPoints
     * @return PlansControl
     */
    public function setMinimumPoints($minimumPoints)
    {
        $this->minimumPoints = $minimumPoints;

        return $this;
    }

    /**
     * Get minimumPoints
     *
     * @return integer 
     */
    public function getMinimumPoints()
    {
        return $this->minimumPoints;
    }

    /**
     * Set maximumPoints
     *
     * @param integer $maximumPoints
     * @return PlansControl
     */
    public function setMaximumPoints($maximumPoints)
    {
        $this->maximumPoints = $maximumPoints;

        return $this;
    }

    /**
     * Get maximumPoints
     *
     * @return integer 
     */
    public function getMaximumPoints()
    {
        return $this->maximumPoints;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return PlansControl
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set discountMarkup
     *
     * @param float $discountMarkup
     * @return PlansControl
     */
    public function setDiscountMarkup($discountMarkup)
    {
        $this->discountMarkup = $discountMarkup;

        return $this;
    }

    /**
     * Get discountMarkup
     *
     * @return float 
     */
    public function getDiscountMarkup()
    {
        return $this->discountMarkup;
    }

    /**
     * Set daysStart
     *
     * @param float $daysStart
     * @return PlansControl
     */
    public function setDaysStart($daysStart)
    {
        $this->daysStart = $daysStart;

        return $this;
    }

    /**
     * Get daysStart
     *
     * @return float 
     */
    public function getDaysStart()
    {
        return $this->daysStart;
    }

    /**
     * Set daysEnd
     *
     * @param float $daysEnd
     * @return PlansControl
     */
    public function setDaysEnd($daysEnd)
    {
        $this->daysEnd = $daysEnd;

        return $this;
    }

    /**
     * Get daysEnd
     *
     * @return float 
     */
    public function getDaysEnd()
    {
        return $this->daysEnd;
    }

    /**
     * Set percentage
     *
     * @param float $percentage
     * @return PlansControl
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
     * Set plansControlConfig
     *
     * @param \PlansControlConfig $plansControlConfig
     * @return PlansControl
     */
    public function setPlansControlConfig(\PlansControlConfig $plansControlConfig = null)
    {
        $this->plansControlConfig = $plansControlConfig;

        return $this;
    }

    /**
     * Get plansControlConfig
     *
     * @return \PlansControlConfig 
     */
    public function getPlansControlConfig()
    {
        return $this->plansControlConfig;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="fixes_amount", type="float", precision=20, scale=2, nullable=true)
     */
    private $fixesAmount = '0.00';


    /**
     * Set fixesAmount
     *
     * @param float $fixesAmount
     * @return PlansControl
     */
    public function setFixesAmount($fixesAmount)
    {
        $this->fixesAmount = $fixesAmount;

        return $this;
    }

    /**
     * Get fixesAmount
     *
     * @return float 
     */
    public function getFixesAmount()
    {
        return $this->fixesAmount;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="use_fixed_value", type="string", length=5, nullable=true)
     */
    private $useFixedValue = 'false';


    /**
     * Set useFixedValue
     *
     * @param string $useFixedValue
     * @return PlansControl
     */
    public function setUseFixedValue($useFixedValue)
    {
        $this->useFixedValue = $useFixedValue;

        return $this;
    }

    /**
     * Get useFixedValue
     *
     * @return string 
     */
    public function getUseFixedValue()
    {
        return $this->useFixedValue;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="discount_type", type="string", nullable=true)
     */
    private $discountType = 'D';


    /**
     * Set discountType
     *
     * @param string $discountType
     * @return PlansControl
     */
    public function setDiscountType($discountType)
    {
        $this->discountType = $discountType;

        return $this;
    }

    /**
     * Get discountType
     *
     * @return string 
     */
    public function getDiscountType()
    {
        return $this->discountType;
    }
}
