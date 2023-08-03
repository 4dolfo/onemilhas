<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Emails
 *
 * @ORM\Table(name="emails", indexes={@ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class Emails
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
     * @ORM\Column(name="status", type="string", length=25, nullable=false)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="partner", type="string", length=80, nullable=false)
     */
    private $partner;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="string", length=10000, nullable=false)
     */
    private $content;

    /**
     * @var string
     *
     * @ORM\Column(name="bcc", type="string", length=80, nullable=true)
     */
    private $bcc;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=200, nullable=false)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50, nullable=false)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_to_send", type="datetime", nullable=false)
     */
    private $dateToSend;

    /**
     * @var string
     *
     * @ORM\Column(name="attachments", type="string", length=150, nullable=true)
     */
    private $attachments;

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
     * Set status
     *
     * @param string $status
     * @return Emails
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
     * Set partner
     *
     * @param string $partner
     * @return Emails
     */
    public function setPartner($partner)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * Get partner
     *
     * @return string 
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Emails
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set bcc
     *
     * @param string $bcc
     * @return Emails
     */
    public function setBcc($bcc)
    {
        $this->bcc = $bcc;

        return $this;
    }

    /**
     * Get bcc
     *
     * @return string 
     */
    public function getBcc()
    {
        return $this->bcc;
    }

    /**
     * Set subject
     *
     * @param string $subject
     * @return Emails
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject
     *
     * @return string 
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Emails
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
     * Set dateToSend
     *
     * @param \DateTime $dateToSend
     * @return Emails
     */
    public function setDateToSend($dateToSend)
    {
        $this->dateToSend = $dateToSend;

        return $this;
    }

    /**
     * Get dateToSend
     *
     * @return \DateTime 
     */
    public function getDateToSend()
    {
        return $this->dateToSend;
    }

    /**
     * Set attachments
     *
     * @param string $attachments
     * @return Emails
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;

        return $this;
    }

    /**
     * Get attachments
     *
     * @return string 
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Set user
     *
     * @param \Businesspartner $user
     * @return Emails
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
