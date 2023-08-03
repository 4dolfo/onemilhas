<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * OnlineNotificationStatus
 *
 * @ORM\Table(name="online_notification_status", indexes={@ORM\Index(name="order_id", columns={"order_id"})})
 * @ORM\Entity
 */
class OnlineNotificationStatus
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
     * @ORM\Column(name="status", type="string", length=255, nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="user", type="string", length=255, nullable=false)
     */
    private $user;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issue_date", type="datetime", nullable=false)
     */
    private $issueDate;

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
     * Set status
     *
     * @param string $status
     * @return OnlineNotificationStatus
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set user
     *
     * @param string $user
     * @return OnlineNotificationStatus
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set issueDate
     *
     * @param \DateTime $issueDate
     * @return OnlineNotificationStatus
     */
    public function setIssueDate($issueDate)
    {
        $this->issueDate = $issueDate;

        return $this;
    }

    /**
     * Get issueDate
     *
     * @return \DateTime 
     */
    public function getIssueDate()
    {
        return $this->issueDate;
    }

    /**
     * Set order
     *
     * @param \OnlineOrder $order
     * @return OnlineNotificationStatus
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
    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string", length=255, nullable=true)
     */
    private $reason;


    /**
     * Set reason
     *
     * @param string $reason
     * @return OnlineNotificationStatus
     */
    public function setReason($reason)
    {
        $this->reason = $reason;

        return $this;
    }

    /**
     * Get reason
     *
     * @return string 
     */
    public function getReason()
    {
        return $this->reason;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="post_data", type="text", nullable=true)
     */
    private $postData;


    /**
     * Set postData
     *
     * @param string $postData
     * @return OnlineNotificationStatus
     */
    public function setPostData($postData)
    {
        $this->postData = $postData;

        return $this;
    }

    /**
     * Get postData
     *
     * @return string 
     */
    public function getPostData()
    {
        return $this->postData;
    }
}
