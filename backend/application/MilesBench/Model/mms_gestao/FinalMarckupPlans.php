<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * FinalMarckupPlans
 *
 * @ORM\Table(name="final_marckup_plans", indexes={@ORM\Index(name="plans_control_config_id", columns={"plans_control_config_id"})})
 * @ORM\Entity
 */
class FinalMarckupPlans
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
     * Set value
     *
     * @param string $value
     * @return FinalMarckupPlans
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
     * @return FinalMarckupPlans
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
