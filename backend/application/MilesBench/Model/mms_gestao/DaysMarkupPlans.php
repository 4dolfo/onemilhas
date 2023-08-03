<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * DaysMarkupPlans
 *
 * @ORM\Table(name="days_markup_plans", indexes={@ORM\Index(name="plans_control_config_id", columns={"plans_control_config_id"}), @ORM\Index(name="plans_control_config_id_2", columns={"plans_control_config_id"})})
 * @ORM\Entity
 */
class DaysMarkupPlans
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
     * @ORM\Column(name="minimum_days", type="integer", nullable=false)
     */
    private $minimumDays;

    /**
     * @var integer
     *
     * @ORM\Column(name="maximum_days", type="integer", nullable=false)
     */
    private $maximumDays;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $value = '0.00';

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
     * Set minimumDays
     *
     * @param integer $minimumDays
     * @return DaysMarkupPlans
     */
    public function setMinimumDays($minimumDays)
    {
        $this->minimumDays = $minimumDays;

        return $this;
    }

    /**
     * Get minimumDays
     *
     * @return integer 
     */
    public function getMinimumDays()
    {
        return $this->minimumDays;
    }

    /**
     * Set maximumDays
     *
     * @param integer $maximumDays
     * @return DaysMarkupPlans
     */
    public function setMaximumDays($maximumDays)
    {
        $this->maximumDays = $maximumDays;

        return $this;
    }

    /**
     * Get maximumDays
     *
     * @return integer 
     */
    public function getMaximumDays()
    {
        return $this->maximumDays;
    }

    /**
     * Set value
     *
     * @param string $value
     * @return DaysMarkupPlans
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
     * Set plansControlConfig
     *
     * @param \PlansControlConfig $plansControlConfig
     * @return DaysMarkupPlans
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
}
