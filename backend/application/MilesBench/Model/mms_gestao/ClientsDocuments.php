<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ClientsDocuments
 *
 * @ORM\Table(name="clients_documents", indexes={@ORM\Index(name="client_id", columns={"client_id"}), @ORM\Index(name="documents_id", columns={"documents_id"}), @ORM\Index(name="businesspartner_id", columns={"businesspartner_id"})})
 * @ORM\Entity
 */
class ClientsDocuments
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
     * @var integer
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="datetime", type="datetime", nullable=false)
     */
    private $datetime;

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
     * @var \DocumentsChecking
     *
     * @ORM\ManyToOne(targetEntity="DocumentsChecking")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="documents_id", referencedColumnName="id")
     * })
     */
    private $documents;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="businesspartner_id", referencedColumnName="id")
     * })
     */
    private $businesspartner;


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
     * @param integer $status
     * @return ClientsDocuments
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set datetime
     *
     * @param \DateTime $datetime
     * @return ClientsDocuments
     */
    public function setDatetime($datetime)
    {
        $this->datetime = $datetime;

        return $this;
    }

    /**
     * Get datetime
     *
     * @return \DateTime 
     */
    public function getDatetime()
    {
        return $this->datetime;
    }

    /**
     * Set client
     *
     * @param \Businesspartner $client
     * @return ClientsDocuments
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
     * Set documents
     *
     * @param \DocumentsChecking $documents
     * @return ClientsDocuments
     */
    public function setDocuments(\DocumentsChecking $documents = null)
    {
        $this->documents = $documents;

        return $this;
    }

    /**
     * Get documents
     *
     * @return \DocumentsChecking 
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * Set businesspartner
     *
     * @param \Businesspartner $businesspartner
     * @return ClientsDocuments
     */
    public function setBusinesspartner(\Businesspartner $businesspartner = null)
    {
        $this->businesspartner = $businesspartner;

        return $this;
    }

    /**
     * Get businesspartner
     *
     * @return \Businesspartner 
     */
    public function getBusinesspartner()
    {
        return $this->businesspartner;
    }
}
