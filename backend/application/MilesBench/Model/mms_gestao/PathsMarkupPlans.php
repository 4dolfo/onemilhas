<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PathsMarkupPlans
 *
 * @ORM\Table(name="paths_markup_plans", indexes={@ORM\Index(name="plans_control_config_id", columns={"plans_control_config_id"})})
 * @ORM\Entity
 */
class PathsMarkupPlans
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
     * @ORM\Column(name="airport_code", type="string", length=45, nullable=false)
     */
    private $airportCode;

    /**
     * @var float
     *
     * @ORM\Column(name="discount", type="float", precision=20, scale=2, nullable=false)
     */
    private $discount = '0.00';

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
     * Set airportCode
     *
     * @param string $airportCode
     * @return PathsMarkupPlans
     */
    public function setAirportCode($airportCode)
    {
        $this->airportCode = $airportCode;

        return $this;
    }

    /**
     * Get airportCode
     *
     * @return string 
     */
    public function getAirportCode()
    {
        return $this->airportCode;
    }

    /**
     * Set discount
     *
     * @param float $discount
     * @return PathsMarkupPlans
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return float 
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set plansControlConfig
     *
     * @param \PlansControlConfig $plansControlConfig
     * @return PathsMarkupPlans
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
