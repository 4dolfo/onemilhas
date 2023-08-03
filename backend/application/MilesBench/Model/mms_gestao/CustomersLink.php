<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CustomersLink
 *
 * @ORM\Table(name="customers_link", indexes={@ORM\Index(name="dealer_fk", columns={"clientDealer"}), @ORM\Index(name="userId_fk", columns={"user_id"})})
 * @ORM\Entity
 */
class CustomersLink
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
     *   @ORM\JoinColumn(name="clientDealer", referencedColumnName="id")
     * })
     */
    private $clientdealer;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;


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
     * Set clientdealer
     *
     * @param \Businesspartner $clientdealer
     * @return CustomersLink
     */
    public function setClientdealer(\Businesspartner $clientdealer = null)
    {
        $this->clientdealer = $clientdealer;

        return $this;
    }

    /**
     * Get clientdealer
     *
     * @return \Businesspartner 
     */
    public function getClientdealer()
    {
        return $this->clientdealer;
    }

    /**
     * Set user
     *
     * @param \Businesspartner $user
     * @return CustomersLink
     */
    public function setUser(\Businesspartner $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Businesspartner 
     */
    public function getUser()
    {
        return $this->user;
    }
}
