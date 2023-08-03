<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * OnlineConnection
 *
 * @ORM\Table(name="online_connection", indexes={@ORM\Index(name="online_flight_id", columns={"online_flight_id"})})
 * @ORM\Entity
 */
class OnlineConnection
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
     * @ORM\Column(name="flight", type="string", length=20, nullable=false)
     */
    private $flight;

    /**
     * @var string
     *
     * @ORM\Column(name="flight_time", type="string", length=5, nullable=false)
     */
    private $flightTime;

    /**
     * @var string
     *
     * @ORM\Column(name="boarding", type="string", length=10, nullable=false)
     */
    private $boarding;

    /**
     * @var string
     *
     * @ORM\Column(name="landing", type="string", length=10, nullable=false)
     */
    private $landing;

    /**
     * @var string
     *
     * @ORM\Column(name="airport_code_from", type="string", length=20, nullable=false)
     */
    private $airportCodeFrom;

    /**
     * @var string
     *
     * @ORM\Column(name="airport_code_to", type="string", length=20, nullable=false)
     */
    private $airportCodeTo;

    /**
     * @var string
     *
     * @ORM\Column(name="seat", type="string", length=6, nullable=false)
     */
    private $seat = '';

    /**
     * @var \OnlineFlight
     *
     * @ORM\ManyToOne(targetEntity="OnlineFlight")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="online_flight_id", referencedColumnName="id")
     * })
     */
    private $onlineFlight;


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
     * Set flight
     *
     * @param string $flight
     * @return OnlineConnection
     */
    public function setFlight($flight)
    {
        $this->flight = $flight;

        return $this;
    }

    /**
     * Get flight
     *
     * @return string 
     */
    public function getFlight()
    {
        return $this->flight;
    }

    /**
     * Set flightTime
     *
     * @param string $flightTime
     * @return OnlineConnection
     */
    public function setFlightTime($flightTime)
    {
        $this->flightTime = $flightTime;

        return $this;
    }

    /**
     * Get flightTime
     *
     * @return string 
     */
    public function getFlightTime()
    {
        return $this->flightTime;
    }

    /**
     * Set boarding
     *
     * @param string $boarding
     * @return OnlineConnection
     */
    public function setBoarding($boarding)
    {
        $this->boarding = $boarding;

        return $this;
    }

    /**
     * Get boarding
     *
     * @return string 
     */
    public function getBoarding()
    {
        return $this->boarding;
    }

    /**
     * Set landing
     *
     * @param string $landing
     * @return OnlineConnection
     */
    public function setLanding($landing)
    {
        $this->landing = $landing;

        return $this;
    }

    /**
     * Get landing
     *
     * @return string 
     */
    public function getLanding()
    {
        return $this->landing;
    }

    /**
     * Set airportCodeFrom
     *
     * @param string $airportCodeFrom
     * @return OnlineConnection
     */
    public function setAirportCodeFrom($airportCodeFrom)
    {
        $this->airportCodeFrom = $airportCodeFrom;

        return $this;
    }

    /**
     * Get airportCodeFrom
     *
     * @return string 
     */
    public function getAirportCodeFrom()
    {
        return $this->airportCodeFrom;
    }

    /**
     * Set airportCodeTo
     *
     * @param string $airportCodeTo
     * @return OnlineConnection
     */
    public function setAirportCodeTo($airportCodeTo)
    {
        $this->airportCodeTo = $airportCodeTo;

        return $this;
    }

    /**
     * Get airportCodeTo
     *
     * @return string 
     */
    public function getAirportCodeTo()
    {
        return $this->airportCodeTo;
    }

    /**
     * Set seat
     *
     * @param string $seat
     * @return OnlineConnection
     */
    public function setSeat($seat)
    {
        $this->seat = $seat;

        return $this;
    }

    /**
     * Get seat
     *
     * @return string 
     */
    public function getSeat()
    {
        return $this->seat;
    }

    /**
     * Set onlineFlight
     *
     * @param \OnlineFlight $onlineFlight
     * @return OnlineConnection
     */
    public function setOnlineFlight(\OnlineFlight $onlineFlight = null)
    {
        $this->onlineFlight = $onlineFlight;

        return $this;
    }

    /**
     * Get onlineFlight
     *
     * @return \OnlineFlight 
     */
    public function getOnlineFlight()
    {
        return $this->onlineFlight;
    }
}
