<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SalePlansAirlinesFixedMarkup
 *
 * @ORM\Table(name="sale_plans_airlines_fixed_markup", indexes={@ORM\Index(name="sale_plans_id", columns={"sale_plans_id"}), @ORM\Index(name="airline_id", columns={"airline_id"})})
 * @ORM\Entity
 */
class SalePlansAirlinesFixedMarkup
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
     * @ORM\Column(name="value", type="float", precision=20, scale=2, nullable=false)
     */
    private $value = '0.00';

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
     * Set value
     *
     * @param float $value
     * @return SalePlansAirlinesFixedMarkup
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
     * Set airline
     *
     * @param \Airline $airline
     * @return SalePlansAirlinesFixedMarkup
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
     * @return SalePlansAirlinesFixedMarkup
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
