<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PlansBaggage
 *
 * @ORM\Table(name="plans_baggage", indexes={@ORM\Index(name="plans_control_config_id", columns={"plans_control_config_id"})})
 * @ORM\Entity
 */
class PlansBaggage
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
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var float
     *
     * @ORM\Column(name="value", type="float", precision=20, scale=2, nullable=false)
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
     * Set amount
     *
     * @param integer $amount
     * @return PlansBaggage
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return integer 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set value
     *
     * @param float $value
     * @return PlansBaggage
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set plansControlConfig
     *
     * @param \PlansControlConfig $plansControlConfig
     * @return PlansBaggage
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
