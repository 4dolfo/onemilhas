<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Cupons
 *
 * @ORM\Table(name="cupons", indexes={@ORM\Index(name="client_id", columns={"client_id"})})
 * @ORM\Entity
 */
class Cupons
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
     * @var float
     *
     * @ORM\Column(name="value", type="float", precision=20, scale=2, nullable=true)
     */
    private $value = '0.00';

    /**
     * @var boolean
     *
     * @ORM\Column(name="used", type="boolean", nullable=false)
     */
    private $used;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_expiracao", type="datetime", nullable=true)
     */
    private $dataExpiracao;

    /**
     * @var string
     *
     * @ORM\Column(name="nome", type="string", length=255, nullable=false)
     */
    private $nome;

    /**
     * @var float
     *
     * @ORM\Column(name="valor_minimo", type="float", precision=10, scale=0, nullable=true)
     */
    private $valorMinimo = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_inicio", type="datetime", nullable=true)
     */
    private $dataInicio;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo_cupom", type="string", nullable=false)
     */
    private $tipoCupom = 'D';

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
     * Set value
     *
     * @param float $value
     * @return Cupons
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value
     *
     * @return float 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set used
     *
     * @param boolean $used
     * @return Cupons
     */
    public function setUsed($used)
    {
        $this->used = $used;

        return $this;
    }

    /**
     * Get used
     *
     * @return boolean 
     */
    public function getUsed()
    {
        return $this->used;
    }

    /**
     * Set dataExpiracao
     *
     * @param \DateTime $dataExpiracao
     * @return Cupons
     */
    public function setDataExpiracao($dataExpiracao)
    {
        $this->dataExpiracao = $dataExpiracao;

        return $this;
    }

    /**
     * Get dataExpiracao
     *
     * @return \DateTime 
     */
    public function getDataExpiracao()
    {
        return $this->dataExpiracao;
    }

    /**
     * Set nome
     *
     * @param string $nome
     * @return Cupons
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
     * Set valorMinimo
     *
     * @param float $valorMinimo
     * @return Cupons
     */
    public function setValorMinimo($valorMinimo)
    {
        $this->valorMinimo = $valorMinimo;

        return $this;
    }

    /**
     * Get valorMinimo
     *
     * @return float 
     */
    public function getValorMinimo()
    {
        return $this->valorMinimo;
    }

    /**
     * Set dataInicio
     *
     * @param \DateTime $dataInicio
     * @return Cupons
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
     * Set tipoCupom
     *
     * @param string $tipoCupom
     * @return Cupons
     */
    public function setTipoCupom($tipoCupom)
    {
        $this->tipoCupom = $tipoCupom;

        return $this;
    }

    /**
     * Get tipoCupom
     *
     * @return string 
     */
    public function getTipoCupom()
    {
        return $this->tipoCupom;
    }

    /**
     * Set client
     *
     * @param \Businesspartner $client
     * @return Cupons
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
     * @var integer
     *
     * @ORM\Column(name="quant_usos", type="integer", nullable=true)
     */
    private $quantUsos = '0';


    /**
     * Set quantUsos
     *
     * @param integer $quantUsos
     * @return Cupons
     */
    public function setQuantUsos($quantUsos)
    {
        $this->quantUsos = $quantUsos;

        return $this;
    }

    /**
     * Get quantUsos
     *
     * @return integer 
     */
    public function getQuantUsos()
    {
        return $this->quantUsos;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="valid_voos", type="string", nullable=true)
     */
    private $validVoos;

    /**
     * @var string
     *
     * @ORM\Column(name="aereas", type="string", length=255, nullable=true)
     */
    private $aereas;


    /**
     * Set validVoos
     *
     * @param string $validVoos
     * @return Cupons
     */
    public function setValidVoos($validVoos)
    {
        $this->validVoos = $validVoos;

        return $this;
    }

    /**
     * Get validVoos
     *
     * @return string 
     */
    public function getValidVoos()
    {
        return $this->validVoos;
    }

    /**
     * Set aereas
     *
     * @param string $aereas
     * @return Cupons
     */
    public function setAereas($aereas)
    {
        $this->aereas = $aereas;

        return $this;
    }

    /**
     * Get aereas
     *
     * @return string 
     */
    public function getAereas()
    {
        return $this->aereas;
    }
    /**
     * @var boolean
     *
     * @ORM\Column(name="milhas", type="boolean", nullable=true)
     */
    private $milhas = '1';

    /**
     * @var boolean
     *
     * @ORM\Column(name="pagante", type="boolean", nullable=true)
     */
    private $pagante = '0';


    /**
     * Set milhas
     *
     * @param boolean $milhas
     * @return Cupons
     */
    public function setMilhas($milhas)
    {
        $this->milhas = $milhas;

        return $this;
    }

    /**
     * Get milhas
     *
     * @return boolean 
     */
    public function getMilhas()
    {
        return $this->milhas;
    }

    /**
     * Set pagante
     *
     * @param boolean $pagante
     * @return Cupons
     */
    public function setPagante($pagante)
    {
        $this->pagante = $pagante;

        return $this;
    }

    /**
     * Get pagante
     *
     * @return boolean 
     */
    public function getPagante()
    {
        return $this->pagante;
    }
}
