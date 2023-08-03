<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * RobotEmissionIn8
 *
 * @ORM\Table(name="robot_emission_in8", indexes={@ORM\Index(name="order_id", columns={"order_id"}), @ORM\Index(name="businesspartner_id", columns={"businesspartner_id"})})
 * @ORM\Entity
 */
class RobotEmissionIn8
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
     * @ORM\Column(name="Identificador", type="string", length=150, nullable=true)
     */
    private $identificador;

    /**
     * @var string
     *
     * @ORM\Column(name="flight_locator", type="string", length=500, nullable=true)
     */
    private $flightLocator;

    /**
     * @var string
     *
     * @ORM\Column(name="file_id", type="string", length=150, nullable=true)
     */
    private $fileId;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=255, nullable=true)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="alerta", type="string", length=255, nullable=true)
     */
    private $alerta;

    /**
     * @var string
     *
     * @ORM\Column(name="erro", type="string", length=500, nullable=true)
     */
    private $erro;

    /**
     * @var string
     *
     * @ORM\Column(name="sucesso", type="string", length=500, nullable=true)
     */
    private $sucesso;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issue_date", type="datetime", nullable=true)
     */
    private $issueDate;

    /**
     * @var string
     *
     * @ORM\Column(name="post", type="text", nullable=true)
     */
    private $post;

    /**
     * @var string
     *
     * @ORM\Column(name="retorno", type="text", nullable=true)
     */
    private $retorno;

    /**
     * @var string
     *
     * @ORM\Column(name="ficha", type="string", length=255, nullable=true)
     */
    private $ficha;

    /**
     * @var string
     *
     * @ORM\Column(name="airline", type="string", length=155, nullable=true)
     */
    private $airline;

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
     * Set identificador
     *
     * @param string $identificador
     * @return RobotEmissionIn8
     */
    public function setIdentificador($identificador)
    {
        $this->identificador = $identificador;

        return $this;
    }

    /**
     * Get identificador
     *
     * @return string 
     */
    public function getIdentificador()
    {
        return $this->identificador;
    }

    /**
     * Set flightLocator
     *
     * @param string $flightLocator
     * @return RobotEmissionIn8
     */
    public function setFlightLocator($flightLocator)
    {
        $this->flightLocator = $flightLocator;

        return $this;
    }

    /**
     * Get flightLocator
     *
     * @return string 
     */
    public function getFlightLocator()
    {
        return $this->flightLocator;
    }

    /**
     * Set fileId
     *
     * @param string $fileId
     * @return RobotEmissionIn8
     */
    public function setFileId($fileId)
    {
        $this->fileId = $fileId;

        return $this;
    }

    /**
     * Get fileId
     *
     * @return string 
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return RobotEmissionIn8
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
     * Set alerta
     *
     * @param string $alerta
     * @return RobotEmissionIn8
     */
    public function setAlerta($alerta)
    {
        $this->alerta = $alerta;

        return $this;
    }

    /**
     * Get alerta
     *
     * @return string 
     */
    public function getAlerta()
    {
        return $this->alerta;
    }

    /**
     * Set erro
     *
     * @param string $erro
     * @return RobotEmissionIn8
     */
    public function setErro($erro)
    {
        $this->erro = $erro;

        return $this;
    }

    /**
     * Get erro
     *
     * @return string 
     */
    public function getErro()
    {
        return $this->erro;
    }

    /**
     * Set sucesso
     *
     * @param string $sucesso
     * @return RobotEmissionIn8
     */
    public function setSucesso($sucesso)
    {
        $this->sucesso = $sucesso;

        return $this;
    }

    /**
     * Get sucesso
     *
     * @return string 
     */
    public function getSucesso()
    {
        return $this->sucesso;
    }

    /**
     * Set issueDate
     *
     * @param \DateTime $issueDate
     * @return RobotEmissionIn8
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
     * Set post
     *
     * @param string $post
     * @return RobotEmissionIn8
     */
    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    /**
     * Get post
     *
     * @return string 
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * Set retorno
     *
     * @param string $retorno
     * @return RobotEmissionIn8
     */
    public function setRetorno($retorno)
    {
        $this->retorno = $retorno;

        return $this;
    }

    /**
     * Get retorno
     *
     * @return string 
     */
    public function getRetorno()
    {
        return $this->retorno;
    }

    /**
     * Set ficha
     *
     * @param string $ficha
     * @return RobotEmissionIn8
     */
    public function setFicha($ficha)
    {
        $this->ficha = $ficha;

        return $this;
    }

    /**
     * Get ficha
     *
     * @return string 
     */
    public function getFicha()
    {
        return $this->ficha;
    }

    /**
     * Set airline
     *
     * @param string $airline
     * @return RobotEmissionIn8
     */
    public function setAirline($airline)
    {
        $this->airline = $airline;

        return $this;
    }

    /**
     * Get airline
     *
     * @return string 
     */
    public function getAirline()
    {
        return $this->airline;
    }

    /**
     * Set order
     *
     * @param \OnlineOrder $order
     * @return RobotEmissionIn8
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
     * Set businesspartner
     *
     * @param \Businesspartner $businesspartner
     * @return RobotEmissionIn8
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
    /**
     * @var integer
     *
     * @ORM\Column(name="online_pax_id", type="integer", nullable=false)
     */
    private $onlinePaxId;

    /**
     * @var integer
     *
     * @ORM\Column(name="online_flight_id", type="integer", nullable=false)
     */
    private $onlineFlightId;


    /**
     * Set onlinePaxId
     *
     * @param integer $onlinePaxId
     * @return RobotEmissionIn8
     */
    public function setOnlinePaxId($onlinePaxId)
    {
        $this->onlinePaxId = $onlinePaxId;

        return $this;
    }

    /**
     * Get onlinePaxId
     *
     * @return integer 
     */
    public function getOnlinePaxId()
    {
        return $this->onlinePaxId;
    }

    /**
     * Set onlineFlightId
     *
     * @param integer $onlineFlightId
     * @return RobotEmissionIn8
     */
    public function setOnlineFlightId($onlineFlightId)
    {
        $this->onlineFlightId = $onlineFlightId;

        return $this;
    }

    /**
     * Get onlineFlightId
     *
     * @return integer 
     */
    public function getOnlineFlightId()
    {
        return $this->onlineFlightId;
    }
}
