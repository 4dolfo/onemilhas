<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CuponsB2c
 *
 * @ORM\Table(name="cupons_b2c", indexes={@ORM\Index(name="dealer_id", columns={"dealer_id"}), @ORM\Index(name="user_aprovacao", columns={"user_aprovacao"})})
 * @ORM\Entity
 */
class CuponsB2c
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
     * @ORM\Column(name="nome", type="string", length=255, nullable=false)
     */
    private $nome;

    /**
     * @var float
     *
     * @ORM\Column(name="valor", type="float", precision=20, scale=2, nullable=false)
     */
    private $valor = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="porcentagem", type="string", length=5, nullable=false)
     */
    private $porcentagem = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=16, nullable=false)
     */
    private $status = 'Criado';

    /**
     * @var string
     *
     * @ORM\Column(name="criado_b2c", type="string", length=5, nullable=false)
     */
    private $criadoB2c = 'false';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_inicio", type="datetime", nullable=true)
     */
    private $dataInicio;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_fim", type="datetime", nullable=true)
     */
    private $dataFim;

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
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_aprovacao", referencedColumnName="id")
     * })
     */
    private $userAprovacao;


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
     * Set nome
     *
     * @param string $nome
     * @return CuponsB2c
     */
    public function setNome($nome)
    {
        $this->nome = $nome;

        return $this;
    }

    /**
     * Get nome
     *
     * @return string 
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * Set valor
     *
     * @param float $valor
     * @return CuponsB2c
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return float 
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set porcentagem
     *
     * @param string $porcentagem
     * @return CuponsB2c
     */
    public function setPorcentagem($porcentagem)
    {
        $this->porcentagem = $porcentagem;

        return $this;
    }

    /**
     * Get porcentagem
     *
     * @return string 
     */
    public function getPorcentagem()
    {
        return $this->porcentagem;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return CuponsB2c
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
     * Set criadoB2c
     *
     * @param string $criadoB2c
     * @return CuponsB2c
     */
    public function setCriadoB2c($criadoB2c)
    {
        $this->criadoB2c = $criadoB2c;

        return $this;
    }

    /**
     * Get criadoB2c
     *
     * @return string 
     */
    public function getCriadoB2c()
    {
        return $this->criadoB2c;
    }

    /**
     * Set dataInicio
     *
     * @param \DateTime $dataInicio
     * @return CuponsB2c
     */
    public function setDataInicio($dataInicio)
    {
        $this->dataInicio = $dataInicio;

        return $this;
    }

    /**
     * Get dataInicio
     *
     * @return \DateTime 
     */
    public function getDataInicio()
    {
        return $this->dataInicio;
    }

    /**
     * Set dataFim
     *
     * @param \DateTime $dataFim
     * @return CuponsB2c
     */
    public function setDataFim($dataFim)
    {
        $this->dataFim = $dataFim;

        return $this;
    }

    /**
     * Get dataFim
     *
     * @return \DateTime 
     */
    public function getDataFim()
    {
        return $this->dataFim;
    }

    /**
     * Set dealer
     *
     * @param \Businesspartner $dealer
     * @return CuponsB2c
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

    /**
     * Set userAprovacao
     *
     * @param \Businesspartner $userAprovacao
     * @return CuponsB2c
     */
    public function setUserAprovacao(\Businesspartner $userAprovacao = null)
    {
        $this->userAprovacao = $userAprovacao;

        return $this;
    }

    /**
     * Get userAprovacao
     *
     * @return \Businesspartner 
     */
    public function getUserAprovacao()
    {
        return $this->userAprovacao;
    }
}
