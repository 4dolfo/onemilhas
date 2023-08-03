<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PlansPromoControlConfig
 *
 * @ORM\Table(name="plans_promo_control_config", indexes={@ORM\Index(name="airline_id", columns={"airline_id"}), @ORM\Index(name="plans_promos_id", columns={"plans_promos_id"})})
 * @ORM\Entity
 */
class PlansPromoControlConfig
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
     * @ORM\Column(name="cost", type="float", precision=20, scale=2, nullable=false)
     */
    private $cost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="config", type="string", length=2048, nullable=false)
     */
    private $config;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=30, nullable=false)
     */
    private $type;

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
     * @var \PlansPromos
     *
     * @ORM\ManyToOne(targetEntity="PlansPromos")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plans_promos_id", referencedColumnName="id")
     * })
     */
    private $plansPromos;


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
     * Set cost
     *
     * @param float $cost
     * @return PlansPromoControlConfig
     */
    public function setCost($cost)
    {
        $this->cost = $cost;

        return $this;
    }

    /**
     * Get cost
     *
     * @return float 
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * Set config
     *
     * @param string $config
     * @return PlansPromoControlConfig
     */
    public function setConfig($config)
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get config
     *
     * @return string 
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return PlansPromoControlConfig
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set airline
     *
     * @param \Airline $airline
     * @return PlansPromoControlConfig
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
     * Set plansPromos
     *
     * @param \PlansPromos $plansPromos
     * @return PlansPromoControlConfig
     */
    public function setPlansPromos(\PlansPromos $plansPromos = null)
    {
        $this->plansPromos = $plansPromos;

        return $this;
    }

    /**
     * Get plansPromos
     *
     * @return \PlansPromos 
     */
    public function getPlansPromos()
    {
        return $this->plansPromos;
    }
}
