<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ClientsDealers
 *
 * @ORM\Table(name="clients_dealers", indexes={@ORM\Index(name="client_id", columns={"client_id"}), @ORM\Index(name="dealer_id", columns={"dealer_id"})})
 * @ORM\Entity
 */
class ClientsDealers
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
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="client_id", referencedColumnName="id")
     * })
     */
    private $client;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dealer_id", referencedColumnName="id")
     * })
     */
    private $dealer;


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
     * Set client
     *
     * @param \Businesspartner $client
     * @return ClientsDealers
     */
    public function setClient(\Businesspartner $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client
     *
     * @return \Businesspartner 
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set dealer
     *
     * @param \Businesspartner $dealer
     * @return ClientsDealers
     */
    public function setDealer(\Businesspartner $dealer = null)
    {
        $this->dealer = $dealer;

        return $this;
    }

    /**
     * Get dealer
     *
     * @return \Businesspartner 
     */
    public function getDealer()
    {
        return $this->dealer;
    }
}
