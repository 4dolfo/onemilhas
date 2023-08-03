<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * OnlineBillets
 *
 * @ORM\Table(name="online_billets", indexes={@ORM\Index(name="order_id", columns={"order_id"})})
 * @ORM\Entity
 */
class OnlineBillets
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
     * @ORM\Column(name="keyname", type="string", length=250, nullable=false)
     */
    private $keyname;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=1024, nullable=false)
     */
    private $url;

    /**
     * @var \OnlineOrder
     *
     * @ORM\ManyToOne(targetEntity="OnlineOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="order_id", referencedColumnName="id")
     * })
     */
    private $order;


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
     * Set keyname
     *
     * @param string $keyname
     * @return OnlineBillets
     */
    public function setKeyname($keyname)
    {
        $this->keyname = $keyname;

        return $this;
    }

    /**
     * Get keyname
     *
     * @return string 
     */
    public function getKeyname()
    {
        return $this->keyname;
    }

    /**
     * Set url
     *
     * @param string $url
     * @return OnlineBillets
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set order
     *
     * @param \OnlineOrder $order
     * @return OnlineBillets
     */
    public function setOrder(\OnlineOrder $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \OnlineOrder 
     */
    public function getOrder()
    {
        return $this->order;
    }
}
