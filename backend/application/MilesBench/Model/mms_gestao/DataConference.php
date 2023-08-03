<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * DataConference
 *
 * @ORM\Table(name="data_conference")
 * @ORM\Entity
 */
class DataConference
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
     * @ORM\Column(name="venda_n", type="string", length=50, nullable=true)
     */
    private $vendaN;

    /**
     * @var string
     *
     * @ORM\Column(name="PAX", type="string", length=100, nullable=true)
     */
    private $pax;

    /**
     * @var string
     *
     * @ORM\Column(name="LOC", type="string", length=15, nullable=true)
     */
    private $loc;

    /**
     * @var string
     *
     * @ORM\Column(name="E_TICKET", type="string", length=30, nullable=true)
     */
    private $eTicket;

    /**
     * @var string
     *
     * @ORM\Column(name="CHECKPLANI", type="string", length=30, nullable=true)
     */
    private $checkplani;

    /**
     * @var string
     *
     * @ORM\Column(name="CIA", type="string", length=10, nullable=true)
     */
    private $cia;

    /**
     * @var string
     *
     * @ORM\Column(name="CARTAO", type="string", length=30, nullable=true)
     */
    private $cartao;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="DATA_EMISSAO", type="datetime", nullable=true)
     */
    private $dataEmissao;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="IDA", type="datetime", nullable=true)
     */
    private $ida;

    /**
     * @var string
     *
     * @ORM\Column(name="ORIGEM", type="string", length=20, nullable=true)
     */
    private $origem;

    /**
     * @var string
     *
     * @ORM\Column(name="VOLTA", type="string", length=50, nullable=true)
     */
    private $volta;

    /**
     * @var string
     *
     * @ORM\Column(name="T_V_FORN", type="string", length=20, nullable=true)
     */
    private $tVForn;

    /**
     * @var string
     *
     * @ORM\Column(name="LOJA", type="string", length=20, nullable=true)
     */
    private $loja;

    /**
     * @var string
     *
     * @ORM\Column(name="PAGAMENTO", type="string", length=30, nullable=true)
     */
    private $pagamento;

    /**
     * @var string
     *
     * @ORM\Column(name="QTD", type="string", length=30, nullable=true)
     */
    private $qtd;

    /**
     * @var string
     *
     * @ORM\Column(name="TAXA", type="string", length=30, nullable=true)
     */
    private $taxa;

    /**
     * @var string
     *
     * @ORM\Column(name="CARTAO_REPASSE", type="string", length=30, nullable=true)
     */
    private $cartaoRepasse;

    /**
     * @var string
     *
     * @ORM\Column(name="REALP", type="string", length=30, nullable=true)
     */
    private $realp;

    /**
     * @var integer
     *
     * @ORM\Column(name="PONTOS", type="integer", nullable=true)
     */
    private $pontos;

    /**
     * @var string
     *
     * @ORM\Column(name="COEFICIENTE", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $coeficiente;

    /**
     * @var string
     *
     * @ORM\Column(name="D_U", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $dU;

    /**
     * @var string
     *
     * @ORM\Column(name="TAXA2", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $taxa2;

    /**
     * @var string
     *
     * @ORM\Column(name="VALOR_CUSTO", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $valorCusto;

    /**
     * @var string
     *
     * @ORM\Column(name="VALOR_PAGO", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $valorPago;

    /**
     * @var string
     *
     * @ORM\Column(name="COMISSAO", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $comissao;

    /**
     * @var string
     *
     * @ORM\Column(name="EMISSOR", type="string", length=20, nullable=true)
     */
    private $emissor;

    /**
     * @var string
     *
     * @ORM\Column(name="CLIENTE", type="string", length=30, nullable=true)
     */
    private $cliente;

    /**
     * @var string
     *
     * @ORM\Column(name="VARIADOS", type="string", length=30, nullable=true)
     */
    private $variados;

    /**
     * @var string
     *
     * @ORM\Column(name="EMISSOR2", type="string", length=30, nullable=true)
     */
    private $emissor2;

    /**
     * @var string
     *
     * @ORM\Column(name="TEMPO", type="string", length=30, nullable=true)
     */
    private $tempo;

    /**
     * @var string
     *
     * @ORM\Column(name="Email", type="string", length=20, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="Obs", type="string", length=20, nullable=true)
     */
    private $obs;

    /**
     * @var string
     *
     * @ORM\Column(name="CONF_DC_TV", type="string", length=30, nullable=true)
     */
    private $confDcTv;


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
     * Set vendaN
     *
     * @param string $vendaN
     * @return DataConference
     */
    public function setVendaN($vendaN)
    {
        $this->vendaN = $vendaN;

        return $this;
    }

    /**
     * Get vendaN
     *
     * @return string 
     */
    public function getVendaN()
    {
        return $this->vendaN;
    }

    /**
     * Set pax
     *
     * @param string $pax
     * @return DataConference
     */
    public function setPax($pax)
    {
        $this->pax = $pax;

        return $this;
    }

    /**
     * Get pax
     *
     * @return string 
     */
    public function getPax()
    {
        return $this->pax;
    }

    /**
     * Set loc
     *
     * @param string $loc
     * @return DataConference
     */
    public function setLoc($loc)
    {
        $this->loc = $loc;

        return $this;
    }

    /**
     * Get loc
     *
     * @return string 
     */
    public function getLoc()
    {
        return $this->loc;
    }

    /**
     * Set eTicket
     *
     * @param string $eTicket
     * @return DataConference
     */
    public function setETicket($eTicket)
    {
        $this->eTicket = $eTicket;

        return $this;
    }

    /**
     * Get eTicket
     *
     * @return string 
     */
    public function getETicket()
    {
        return $this->eTicket;
    }

    /**
     * Set checkplani
     *
     * @param string $checkplani
     * @return DataConference
     */
    public function setCheckplani($checkplani)
    {
        $this->checkplani = $checkplani;

        return $this;
    }

    /**
     * Get checkplani
     *
     * @return string 
     */
    public function getCheckplani()
    {
        return $this->checkplani;
    }

    /**
     * Set cia
     *
     * @param string $cia
     * @return DataConference
     */
    public function setCia($cia)
    {
        $this->cia = $cia;

        return $this;
    }

    /**
     * Get cia
     *
     * @return string 
     */
    public function getCia()
    {
        return $this->cia;
    }

    /**
     * Set cartao
     *
     * @param string $cartao
     * @return DataConference
     */
    public function setCartao($cartao)
    {
        $this->cartao = $cartao;

        return $this;
    }

    /**
     * Get cartao
     *
     * @return string 
     */
    public function getCartao()
    {
        return $this->cartao;
    }

    /**
     * Set dataEmissao
     *
     * @param \DateTime $dataEmissao
     * @return DataConference
     */
    public function setDataEmissao($dataEmissao)
    {
        $this->dataEmissao = $dataEmissao;

        return $this;
    }

    /**
     * Get dataEmissao
     *
     * @return \DateTime 
     */
    public function getDataEmissao()
    {
        return $this->dataEmissao;
    }

    /**
     * Set ida
     *
     * @param \DateTime $ida
     * @return DataConference
     */
    public function setIda($ida)
    {
        $this->ida = $ida;

        return $this;
    }

    /**
     * Get ida
     *
     * @return \DateTime 
     */
    public function getIda()
    {
        return $this->ida;
    }

    /**
     * Set origem
     *
     * @param string $origem
     * @return DataConference
     */
    public function setOrigem($origem)
    {
        $this->origem = $origem;

        return $this;
    }

    /**
     * Get origem
     *
     * @return string 
     */
    public function getOrigem()
    {
        return $this->origem;
    }

    /**
     * Set volta
     *
     * @param string $volta
     * @return DataConference
     */
    public function setVolta($volta)
    {
        $this->volta = $volta;

        return $this;
    }

    /**
     * Get volta
     *
     * @return string 
     */
    public function getVolta()
    {
        return $this->volta;
    }

    /**
     * Set tVForn
     *
     * @param string $tVForn
     * @return DataConference
     */
    public function setTVForn($tVForn)
    {
        $this->tVForn = $tVForn;

        return $this;
    }

    /**
     * Get tVForn
     *
     * @return string 
     */
    public function getTVForn()
    {
        return $this->tVForn;
    }

    /**
     * Set loja
     *
     * @param string $loja
     * @return DataConference
     */
    public function setLoja($loja)
    {
        $this->loja = $loja;

        return $this;
    }

    /**
     * Get loja
     *
     * @return string 
     */
    public function getLoja()
    {
        return $this->loja;
    }

    /**
     * Set pagamento
     *
     * @param string $pagamento
     * @return DataConference
     */
    public function setPagamento($pagamento)
    {
        $this->pagamento = $pagamento;

        return $this;
    }

    /**
     * Get pagamento
     *
     * @return string 
     */
    public function getPagamento()
    {
        return $this->pagamento;
    }

    /**
     * Set qtd
     *
     * @param string $qtd
     * @return DataConference
     */
    public function setQtd($qtd)
    {
        $this->qtd = $qtd;

        return $this;
    }

    /**
     * Get qtd
     *
     * @return string 
     */
    public function getQtd()
    {
        return $this->qtd;
    }

    /**
     * Set taxa
     *
     * @param string $taxa
     * @return DataConference
     */
    public function setTaxa($taxa)
    {
        $this->taxa = $taxa;

        return $this;
    }

    /**
     * Get taxa
     *
     * @return string 
     */
    public function getTaxa()
    {
        return $this->taxa;
    }

    /**
     * Set cartaoRepasse
     *
     * @param string $cartaoRepasse
     * @return DataConference
     */
    public function setCartaoRepasse($cartaoRepasse)
    {
        $this->cartaoRepasse = $cartaoRepasse;

        return $this;
    }

    /**
     * Get cartaoRepasse
     *
     * @return string 
     */
    public function getCartaoRepasse()
    {
        return $this->cartaoRepasse;
    }

    /**
     * Set realp
     *
     * @param string $realp
     * @return DataConference
     */
    public function setRealp($realp)
    {
        $this->realp = $realp;

        return $this;
    }

    /**
     * Get realp
     *
     * @return string 
     */
    public function getRealp()
    {
        return $this->realp;
    }

    /**
     * Set pontos
     *
     * @param integer $pontos
     * @return DataConference
     */
    public function setPontos($pontos)
    {
        $this->pontos = $pontos;

        return $this;
    }

    /**
     * Get pontos
     *
     * @return integer 
     */
    public function getPontos()
    {
        return $this->pontos;
    }

    /**
     * Set coeficiente
     *
     * @param string $coeficiente
     * @return DataConference
     */
    public function setCoeficiente($coeficiente)
    {
        $this->coeficiente = $coeficiente;

        return $this;
    }

    /**
     * Get coeficiente
     *
     * @return string 
     */
    public function getCoeficiente()
    {
        return $this->coeficiente;
    }

    /**
     * Set dU
     *
     * @param string $dU
     * @return DataConference
     */
    public function setDU($dU)
    {
        $this->dU = $dU;

        return $this;
    }

    /**
     * Get dU
     *
     * @return string 
     */
    public function getDU()
    {
        return $this->dU;
    }

    /**
     * Set taxa2
     *
     * @param string $taxa2
     * @return DataConference
     */
    public function setTaxa2($taxa2)
    {
        $this->taxa2 = $taxa2;

        return $this;
    }

    /**
     * Get taxa2
     *
     * @return string 
     */
    public function getTaxa2()
    {
        return $this->taxa2;
    }

    /**
     * Set valorCusto
     *
     * @param string $valorCusto
     * @return DataConference
     */
    public function setValorCusto($valorCusto)
    {
        $this->valorCusto = $valorCusto;

        return $this;
    }

    /**
     * Get valorCusto
     *
     * @return string 
     */
    public function getValorCusto()
    {
        return $this->valorCusto;
    }

    /**
     * Set valorPago
     *
     * @param string $valorPago
     * @return DataConference
     */
    public function setValorPago($valorPago)
    {
        $this->valorPago = $valorPago;

        return $this;
    }

    /**
     * Get valorPago
     *
     * @return string 
     */
    public function getValorPago()
    {
        return $this->valorPago;
    }

    /**
     * Set comissao
     *
     * @param string $comissao
     * @return DataConference
     */
    public function setComissao($comissao)
    {
        $this->comissao = $comissao;

        return $this;
    }

    /**
     * Get comissao
     *
     * @return string 
     */
    public function getComissao()
    {
        return $this->comissao;
    }

    /**
     * Set emissor
     *
     * @param string $emissor
     * @return DataConference
     */
    public function setEmissor($emissor)
    {
        $this->emissor = $emissor;

        return $this;
    }

    /**
     * Get emissor
     *
     * @return string 
     */
    public function getEmissor()
    {
        return $this->emissor;
    }

    /**
     * Set cliente
     *
     * @param string $cliente
     * @return DataConference
     */
    public function setCliente($cliente)
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * Get cliente
     *
     * @return string 
     */
    public function getCliente()
    {
        return $this->cliente;
    }

    /**
     * Set variados
     *
     * @param string $variados
     * @return DataConference
     */
    public function setVariados($variados)
    {
        $this->variados = $variados;

        return $this;
    }

    /**
     * Get variados
     *
     * @return string 
     */
    public function getVariados()
    {
        return $this->variados;
    }

    /**
     * Set emissor2
     *
     * @param string $emissor2
     * @return DataConference
     */
    public function setEmissor2($emissor2)
    {
        $this->emissor2 = $emissor2;

        return $this;
    }

    /**
     * Get emissor2
     *
     * @return string 
     */
    public function getEmissor2()
    {
        return $this->emissor2;
    }

    /**
     * Set tempo
     *
     * @param string $tempo
     * @return DataConference
     */
    public function setTempo($tempo)
    {
        $this->tempo = $tempo;

        return $this;
    }

    /**
     * Get tempo
     *
     * @return string 
     */
    public function getTempo()
    {
        return $this->tempo;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return DataConference
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set obs
     *
     * @param string $obs
     * @return DataConference
     */
    public function setObs($obs)
    {
        $this->obs = $obs;

        return $this;
    }

    /**
     * Get obs
     *
     * @return string 
     */
    public function getObs()
    {
        return $this->obs;
    }

    /**
     * Set confDcTv
     *
     * @param string $confDcTv
     * @return DataConference
     */
    public function setConfDcTv($confDcTv)
    {
        $this->confDcTv = $confDcTv;

        return $this;
    }

    /**
     * Get confDcTv
     *
     * @return string 
     */
    public function getConfDcTv()
    {
        return $this->confDcTv;
    }
}
