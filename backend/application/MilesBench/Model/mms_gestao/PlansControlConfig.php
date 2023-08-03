<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * PlansControlConfig
 *
 * @ORM\Table(name="plans_control_config", indexes={@ORM\Index(name="airline_id", columns={"airline_id"}), @ORM\Index(name="sale_plans_id", columns={"sale_plans_id"})})
 * @ORM\Entity
 */
class PlansControlConfig
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
     * @var boolean
     *
     * @ORM\Column(name="status", type="boolean", nullable=false)
     */
    private $status;

    /**
     * @var float
     *
     * @ORM\Column(name="cost", type="float", precision=20, scale=2, nullable=false)
     */
    private $cost = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="markup", type="float", precision=20, scale=2, nullable=false)
     */
    private $markup = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="tax_baby", type="float", precision=20, scale=2, nullable=false)
     */
    private $taxBaby = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="boarding_tax", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $boardingTax = '0.00';

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
     * @var \SalePlans
     *
     * @ORM\ManyToOne(targetEntity="SalePlans")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="sale_plans_id", referencedColumnName="id")
     * })
     */
    private $salePlans;


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
     * Set status
     *
     * @param boolean $status
     * @return PlansControlConfig
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return boolean 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set cost
     *
     * @param float $cost
     * @return PlansControlConfig
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
     * Set markup
     *
     * @param float $markup
     * @return PlansControlConfig
     */
    public function setMarkup($markup)
    {
        $this->markup = $markup;

        return $this;
    }

    /**
     * Get markup
     *
     * @return float 
     */
    public function getMarkup()
    {
        return $this->markup;
    }

    /**
     * Set taxBaby
     *
     * @param float $taxBaby
     * @return PlansControlConfig
     */
    public function setTaxBaby($taxBaby)
    {
        $this->taxBaby = $taxBaby;

        return $this;
    }

    /**
     * Get taxBaby
     *
     * @return float 
     */
    public function getTaxBaby()
    {
        return $this->taxBaby;
    }

    /**
     * Set boardingTax
     *
     * @param string $boardingTax
     * @return PlansControlConfig
     */
    public function setBoardingTax($boardingTax)
    {
        $this->boardingTax = $boardingTax;

        return $this;
    }

    /**
     * Get boardingTax
     *
     * @return string 
     */
    public function getBoardingTax()
    {
        return $this->boardingTax;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return PlansControlConfig
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
     * @return PlansControlConfig
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
     * Set salePlans
     *
     * @param \SalePlans $salePlans
     * @return PlansControlConfig
     */
    public function setSalePlans(\SalePlans $salePlans = null)
    {
        $this->salePlans = $salePlans;

        return $this;
    }

    /**
     * Get salePlans
     *
     * @return \SalePlans 
     */
    public function getSalePlans()
    {
        return $this->salePlans;
    }
}
