<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ClientNotification
 *
 * @ORM\Table(name="client_notification", indexes={@ORM\Index(name="client_id", columns={"client_id"}), @ORM\Index(name="notification_id", columns={"notification_id"})})
 * @ORM\Entity
 */
class ClientNotification
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
     * @var \Notifications
     *
     * @ORM\ManyToOne(targetEntity="Notifications")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="notification_id", referencedColumnName="id")
     * })
     */
    private $notification;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set notification
     *
     * @param \Notifications $notification
     * @return ClientNotification
     */
    public function setNotification(\Notifications $notification = null)
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * Get notification
     *
     * @return \Notifications 
     */
    public function getNotification()
    {
        return $this->notification;
    }

    /**
     * Set client
     *
     * @param \Businesspartner $client
     * @return ClientNotification
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
}
