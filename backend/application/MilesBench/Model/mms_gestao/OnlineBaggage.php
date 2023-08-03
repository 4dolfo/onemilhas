<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * OnlineBaggage
 *
 * @ORM\Table(name="online_baggage", indexes={@ORM\Index(name="online_flight_id", columns={"online_flight_id"}), @ORM\Index(name="online_pax_id", columns={"online_pax_id"})})
 * @ORM\Entity
 */
class OnlineBaggage
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
     * @ORM\Column(name="amount", type="string", length=50, nullable=true)
     */
    private $amount;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", precision=20, scale=2, nullable=false)
     */
    private $price = '0.00';

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
     * @var \OnlinePax
     *
     * @ORM\ManyToOne(targetEntity="OnlinePax")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="online_pax_id", referencedColumnName="id")
     * })
     */
    private $onlinePax;


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
     * @param string $amount
     * @return OnlineBaggage
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set price
     *
     * @param float $price
     * @return OnlineBaggage
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set onlineFlight
     *
     * @param \OnlineFlight $onlineFlight
     * @return OnlineBaggage
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

    /**
     * Set onlinePax
     *
     * @param \OnlinePax $onlinePax
     * @return OnlineBaggage
     */
    public function setOnlinePax(\OnlinePax $onlinePax = null)
    {
        $this->onlinePax = $onlinePax;

        return $this;
    }

    /**
     * Get onlinePax
     *
     * @return \OnlinePax 
     */
    public function getOnlinePax()
    {
        return $this->onlinePax;
    }
}
