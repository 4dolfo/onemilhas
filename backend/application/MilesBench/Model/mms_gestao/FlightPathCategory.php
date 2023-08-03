<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * FlightPathCategory
 *
 * @ORM\Table(name="flight_path_category", indexes={@ORM\Index(name="flight_category_id", columns={"flight_category_id"})})
 * @ORM\Entity
 */
class FlightPathCategory
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
     * @ORM\Column(name="flight_from", type="string", length=20, nullable=false)
     */
    private $flightFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="flight_to", type="string", length=20, nullable=false)
     */
    private $flightTo;

    /**
     * @var \AzulFlightCategory
     *
     * @ORM\ManyToOne(targetEntity="AzulFlightCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="flight_category_id", referencedColumnName="id")
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
     * Set flightFrom
     *
     * @param string $flightFrom
     * @return FlightPathCategory
     */
    public function setFlightFrom($flightFrom)
    {
        $this->flightFrom = $flightFrom;

        return $this;
    }

    /**
     * Get flightFrom
     *
     * @return string 
     */
    public function getFlightFrom()
    {
        return $this->flightFrom;
    }

    /**
     * Set flightTo
     *
     * @param string $flightTo
     * @return FlightPathCategory
     */
    public function setFlightTo($flightTo)
    {
        $this->flightTo = $flightTo;

        return $this;
    }

    /**
     * Get flightTo
     *
     * @return string 
     */
    public function getFlightTo()
    {
        return $this->flightTo;
    }

    /**
     * Set flightCategory
     *
     * @param \AzulFlightCategory $flightCategory
     * @return FlightPathCategory
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
}
