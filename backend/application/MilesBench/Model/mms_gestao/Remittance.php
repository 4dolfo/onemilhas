<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Remittance
 *
 * @ORM\Table(name="remittance")
 * @ORM\Entity
 */
class Remittance
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
     * @ORM\Column(name="agencia", type="string", length=128, nullable=false)
     */
    private $agencia;

    /**
     * @var string
     *
     * @ORM\Column(name="agencia_dv", type="string", length=128, nullable=false)
     */
    private $agenciaDv;

    /**
     * @var string
     *
     * @ORM\Column(name="razao_conta_corrente", type="string", length=128, nullable=false)
     */
    private $razaoContaCorrente;

    /**
     * @var string
     *
     * @ORM\Column(name="carteira", type="string", length=128, nullable=false)
     */
    private $carteira;

    /**
     * @var string
     *
     * @ORM\Column(name="conta", type="string", length=128, nullable=false)
     */
    private $conta;

    /**
     * @var string
     *
     * @ORM\Column(name="conta_dv", type="string", length=128, nullable=false)
     */
    private $contaDv;

    /**
     * @var string
     *
     * @ORM\Column(name="identificacao_empresa", type="string", length=128, nullable=false)
     */
    private $identificacaoEmpresa;

    /**
     * @var string
     *
     * @ORM\Column(name="numero_controle", type="string", length=128, nullable=false)
     */
    private $numeroControle;

    /**
     * @var string
     *
     * @ORM\Column(name="habilitar_debito_compensacao", type="string", length=128, nullable=false)
     */
    private $habilitarDebitoCompensacao;

    /**
     * @var string
     *
     * @ORM\Column(name="habilitar_multa", type="string", length=128, nullable=false)
     */
    private $habilitarMulta;

    /**
     * @var string
     *
     * @ORM\Column(name="percentual_multa", type="string", length=128, nullable=false)
     */
    private $percentualMulta;

    /**
     * @var string
     *
     * @ORM\Column(name="nosso_numero", type="string", length=128, nullable=false)
     */
    private $nossoNumero;

    /**
     * @var string
     *
     * @ORM\Column(name="nosso_numero_dv", type="string", length=128, nullable=false)
     */
    private $nossoNumeroDv;

    /**
     * @var string
     *
     * @ORM\Column(name="desconto_dia", type="string", length=128, nullable=false)
     */
    private $descontoDia;

    /**
     * @var string
     *
     * @ORM\Column(name="rateio", type="string", length=128, nullable=false)
     */
    private $rateio;

    /**
     * @var string
     *
     * @ORM\Column(name="numero_documento", type="string", length=128, nullable=false)
     */
    private $numeroDocumento;

    /**
     * @var string
     *
     * @ORM\Column(name="vencimento", type="string", length=128, nullable=false)
     */
    private $vencimento;

    /**
     * @var string
     *
     * @ORM\Column(name="valor", type="string", length=128, nullable=false)
     */
    private $valor;

    /**
     * @var string
     *
     * @ORM\Column(name="data_emissao_titulo", type="string", length=128, nullable=false)
     */
    private $dataEmissaoTitulo;

    /**
     * @var string
     *
     * @ORM\Column(name="valor_dia_atraso", type="string", length=128, nullable=false)
     */
    private $valorDiaAtraso;

    /**
     * @var string
     *
     * @ORM\Column(name="data_limite_desconto", type="string", length=128, nullable=false)
     */
    private $dataLimiteDesconto;

    /**
     * @var string
     *
     * @ORM\Column(name="valor_desconto", type="string", length=128, nullable=false)
     */
    private $valorDesconto;

    /**
     * @var string
     *
     * @ORM\Column(name="valor_iof", type="string", length=128, nullable=false)
     */
    private $valorIof;

    /**
     * @var string
     *
     * @ORM\Column(name="valor_abatimento_concedido", type="string", length=128, nullable=false)
     */
    private $valorAbatimentoConcedido;

    /**
     * @var string
     *
     * @ORM\Column(name="tipo_inscricao_pagador", type="string", length=128, nullable=false)
     */
    private $tipoInscricaoPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="numero_inscricao", type="string", length=128, nullable=false)
     */
    private $numeroInscricao;

    /**
     * @var string
     *
     * @ORM\Column(name="nome_pagador", type="string", length=128, nullable=false)
     */
    private $nomePagador;

    /**
     * @var string
     *
     * @ORM\Column(name="endereco_pagador", type="string", length=128, nullable=false)
     */
    private $enderecoPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="primeira_mensagem", type="string", length=128, nullable=false)
     */
    private $primeiraMensagem;

    /**
     * @var string
     *
     * @ORM\Column(name="cep_pagador", type="string", length=128, nullable=false)
     */
    private $cepPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="sufixo_cep_pagador", type="string", length=128, nullable=false)
     */
    private $sufixoCepPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="sacador_segunda_mensagem", type="string", length=128, nullable=false)
     */
    private $sacadorSegundaMensagem;

    /**
     * @var string
     *
     * @ORM\Column(name="numero_remessa", type="string", length=128, nullable=false)
     */
    private $numeroRemessa;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issue_date", type="datetime", nullable=false)
     */
    private $issueDate;

    /**
     * @var integer
     *
     * @ORM\Column(name="remittance_sequential", type="integer", nullable=false)
     */
    private $remittanceSequential;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=2, nullable=true)
     */
    private $status;


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
     * Set agencia
     *
     * @param string $agencia
     * @return Remittance
     */
    public function setAgencia($agencia)
    {
        $this->agencia = $agencia;

        return $this;
    }

    /**
     * Get agencia
     *
     * @return string 
     */
    public function getAgencia()
    {
        return $this->agencia;
    }

    /**
     * Set agenciaDv
     *
     * @param string $agenciaDv
     * @return Remittance
     */
    public function setAgenciaDv($agenciaDv)
    {
        $this->agenciaDv = $agenciaDv;

        return $this;
    }

    /**
     * Get agenciaDv
     *
     * @return string 
     */
    public function getAgenciaDv()
    {
        return $this->agenciaDv;
    }

    /**
     * Set razaoContaCorrente
     *
     * @param string $razaoContaCorrente
     * @return Remittance
     */
    public function setRazaoContaCorrente($razaoContaCorrente)
    {
        $this->razaoContaCorrente = $razaoContaCorrente;

        return $this;
    }

    /**
     * Get razaoContaCorrente
     *
     * @return string 
     */
    public function getRazaoContaCorrente()
    {
        return $this->razaoContaCorrente;
    }

    /**
     * Set carteira
     *
     * @param string $carteira
     * @return Remittance
     */
    public function setCarteira($carteira)
    {
        $this->carteira = $carteira;

        return $this;
    }

    /**
     * Get carteira
     *
     * @return string 
     */
    public function getCarteira()
    {
        return $this->carteira;
    }

    /**
     * Set conta
     *
     * @param string $conta
     * @return Remittance
     */
    public function setConta($conta)
    {
        $this->conta = $conta;

        return $this;
    }

    /**
     * Get conta
     *
     * @return string 
     */
    public function getConta()
    {
        return $this->conta;
    }

    /**
     * Set contaDv
     *
     * @param string $contaDv
     * @return Remittance
     */
    public function setContaDv($contaDv)
    {
        $this->contaDv = $contaDv;

        return $this;
    }

    /**
     * Get contaDv
     *
     * @return string 
     */
    public function getContaDv()
    {
        return $this->contaDv;
    }

    /**
     * Set identificacaoEmpresa
     *
     * @param string $identificacaoEmpresa
     * @return Remittance
     */
    public function setIdentificacaoEmpresa($identificacaoEmpresa)
    {
        $this->identificacaoEmpresa = $identificacaoEmpresa;

        return $this;
    }

    /**
     * Get identificacaoEmpresa
     *
     * @return string 
     */
    public function getIdentificacaoEmpresa()
    {
        return $this->identificacaoEmpresa;
    }

    /**
     * Set numeroControle
     *
     * @param string $numeroControle
     * @return Remittance
     */
    public function setNumeroControle($numeroControle)
    {
        $this->numeroControle = $numeroControle;

        return $this;
    }

    /**
     * Get numeroControle
     *
     * @return string 
     */
    public function getNumeroControle()
    {
        return $this->numeroControle;
    }

    /**
     * Set habilitarDebitoCompensacao
     *
     * @param string $habilitarDebitoCompensacao
     * @return Remittance
     */
    public function setHabilitarDebitoCompensacao($habilitarDebitoCompensacao)
    {
        $this->habilitarDebitoCompensacao = $habilitarDebitoCompensacao;

        return $this;
    }

    /**
     * Get habilitarDebitoCompensacao
     *
     * @return string 
     */
    public function getHabilitarDebitoCompensacao()
    {
        return $this->habilitarDebitoCompensacao;
    }

    /**
     * Set habilitarMulta
     *
     * @param string $habilitarMulta
     * @return Remittance
     */
    public function setHabilitarMulta($habilitarMulta)
    {
        $this->habilitarMulta = $habilitarMulta;

        return $this;
    }

    /**
     * Get habilitarMulta
     *
     * @return string 
     */
    public function getHabilitarMulta()
    {
        return $this->habilitarMulta;
    }

    /**
     * Set percentualMulta
     *
     * @param string $percentualMulta
     * @return Remittance
     */
    public function setPercentualMulta($percentualMulta)
    {
        $this->percentualMulta = $percentualMulta;

        return $this;
    }

    /**
     * Get percentualMulta
     *
     * @return string 
     */
    public function getPercentualMulta()
    {
        return $this->percentualMulta;
    }

    /**
     * Set nossoNumero
     *
     * @param string $nossoNumero
     * @return Remittance
     */
    public function setNossoNumero($nossoNumero)
    {
        $this->nossoNumero = $nossoNumero;

        return $this;
    }

    /**
     * Get nossoNumero
     *
     * @return string 
     */
    public function getNossoNumero()
    {
        return $this->nossoNumero;
    }

    /**
     * Set nossoNumeroDv
     *
     * @param string $nossoNumeroDv
     * @return Remittance
     */
    public function setNossoNumeroDv($nossoNumeroDv)
    {
        $this->nossoNumeroDv = $nossoNumeroDv;

        return $this;
    }

    /**
     * Get nossoNumeroDv
     *
     * @return string 
     */
    public function getNossoNumeroDv()
    {
        return $this->nossoNumeroDv;
    }

    /**
     * Set descontoDia
     *
     * @param string $descontoDia
     * @return Remittance
     */
    public function setDescontoDia($descontoDia)
    {
        $this->descontoDia = $descontoDia;

        return $this;
    }

    /**
     * Get descontoDia
     *
     * @return string 
     */
    public function getDescontoDia()
    {
        return $this->descontoDia;
    }

    /**
     * Set rateio
     *
     * @param string $rateio
     * @return Remittance
     */
    public function setRateio($rateio)
    {
        $this->rateio = $rateio;

        return $this;
    }

    /**
     * Get rateio
     *
     * @return string 
     */
    public function getRateio()
    {
        return $this->rateio;
    }

    /**
     * Set numeroDocumento
     *
     * @param string $numeroDocumento
     * @return Remittance
     */
    public function setNumeroDocumento($numeroDocumento)
    {
        $this->numeroDocumento = $numeroDocumento;

        return $this;
    }

    /**
     * Get numeroDocumento
     *
     * @return string 
     */
    public function getNumeroDocumento()
    {
        return $this->numeroDocumento;
    }

    /**
     * Set vencimento
     *
     * @param string $vencimento
     * @return Remittance
     */
    public function setVencimento($vencimento)
    {
        $this->vencimento = $vencimento;

        return $this;
    }

    /**
     * Get vencimento
     *
     * @return string 
     */
    public function getVencimento()
    {
        return $this->vencimento;
    }

    /**
     * Set valor
     *
     * @param string $valor
     * @return Remittance
     */
    public function setValor($valor)
    {
        $this->valor = $valor;

        return $this;
    }

    /**
     * Get valor
     *
     * @return string 
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * Set dataEmissaoTitulo
     *
     * @param string $dataEmissaoTitulo
     * @return Remittance
     */
    public function setDataEmissaoTitulo($dataEmissaoTitulo)
    {
        $this->dataEmissaoTitulo = $dataEmissaoTitulo;

        return $this;
    }

    /**
     * Get dataEmissaoTitulo
     *
     * @return string 
     */
    public function getDataEmissaoTitulo()
    {
        return $this->dataEmissaoTitulo;
    }

    /**
     * Set valorDiaAtraso
     *
     * @param string $valorDiaAtraso
     * @return Remittance
     */
    public function setValorDiaAtraso($valorDiaAtraso)
    {
        $this->valorDiaAtraso = $valorDiaAtraso;

        return $this;
    }

    /**
     * Get valorDiaAtraso
     *
     * @return string 
     */
    public function getValorDiaAtraso()
    {
        return $this->valorDiaAtraso;
    }

    /**
     * Set dataLimiteDesconto
     *
     * @param string $dataLimiteDesconto
     * @return Remittance
     */
    public function setDataLimiteDesconto($dataLimiteDesconto)
    {
        $this->dataLimiteDesconto = $dataLimiteDesconto;

        return $this;
    }

    /**
     * Get dataLimiteDesconto
     *
     * @return string 
     */
    public function getDataLimiteDesconto()
    {
        return $this->dataLimiteDesconto;
    }

    /**
     * Set valorDesconto
     *
     * @param string $valorDesconto
     * @return Remittance
     */
    public function setValorDesconto($valorDesconto)
    {
        $this->valorDesconto = $valorDesconto;

        return $this;
    }

    /**
     * Get valorDesconto
     *
     * @return string 
     */
    public function getValorDesconto()
    {
        return $this->valorDesconto;
    }

    /**
     * Set valorIof
     *
     * @param string $valorIof
     * @return Remittance
     */
    public function setValorIof($valorIof)
    {
        $this->valorIof = $valorIof;

        return $this;
    }

    /**
     * Get valorIof
     *
     * @return string 
     */
    public function getValorIof()
    {
        return $this->valorIof;
    }

    /**
     * Set valorAbatimentoConcedido
     *
     * @param string $valorAbatimentoConcedido
     * @return Remittance
     */
    public function setValorAbatimentoConcedido($valorAbatimentoConcedido)
    {
        $this->valorAbatimentoConcedido = $valorAbatimentoConcedido;

        return $this;
    }

    /**
     * Get valorAbatimentoConcedido
     *
     * @return string 
     */
    public function getValorAbatimentoConcedido()
    {
        return $this->valorAbatimentoConcedido;
    }

    /**
     * Set tipoInscricaoPagador
     *
     * @param string $tipoInscricaoPagador
     * @return Remittance
     */
    public function setTipoInscricaoPagador($tipoInscricaoPagador)
    {
        $this->tipoInscricaoPagador = $tipoInscricaoPagador;

        return $this;
    }

    /**
     * Get tipoInscricaoPagador
     *
     * @return string 
     */
    public function getTipoInscricaoPagador()
    {
        return $this->tipoInscricaoPagador;
    }

    /**
     * Set numeroInscricao
     *
     * @param string $numeroInscricao
     * @return Remittance
     */
    public function setNumeroInscricao($numeroInscricao)
    {
        $this->numeroInscricao = $numeroInscricao;

        return $this;
    }

    /**
     * Get numeroInscricao
     *
     * @return string 
     */
    public function getNumeroInscricao()
    {
        return $this->numeroInscricao;
    }

    /**
     * Set nomePagador
     *
     * @param string $nomePagador
     * @return Remittance
     */
    public function setNomePagador($nomePagador)
    {
        $this->nomePagador = $nomePagador;

        return $this;
    }

    /**
     * Get nomePagador
     *
     * @return string 
     */
    public function getNomePagador()
    {
        return $this->nomePagador;
    }

    /**
     * Set enderecoPagador
     *
     * @param string $enderecoPagador
     * @return Remittance
     */
    public function setEnderecoPagador($enderecoPagador)
    {
        $this->enderecoPagador = $enderecoPagador;

        return $this;
    }

    /**
     * Get enderecoPagador
     *
     * @return string 
     */
    public function getEnderecoPagador()
    {
        return $this->enderecoPagador;
    }

    /**
     * Set primeiraMensagem
     *
     * @param string $primeiraMensagem
     * @return Remittance
     */
    public function setPrimeiraMensagem($primeiraMensagem)
    {
        $this->primeiraMensagem = $primeiraMensagem;

        return $this;
    }

    /**
     * Get primeiraMensagem
     *
     * @return string 
     */
    public function getPrimeiraMensagem()
    {
        return $this->primeiraMensagem;
    }

    /**
     * Set cepPagador
     *
     * @param string $cepPagador
     * @return Remittance
     */
    public function setCepPagador($cepPagador)
    {
        $this->cepPagador = $cepPagador;

        return $this;
    }

    /**
     * Get cepPagador
     *
     * @return string 
     */
    public function getCepPagador()
    {
        return $this->cepPagador;
    }

    /**
     * Set sufixoCepPagador
     *
     * @param string $sufixoCepPagador
     * @return Remittance
     */
    public function setSufixoCepPagador($sufixoCepPagador)
    {
        $this->sufixoCepPagador = $sufixoCepPagador;

        return $this;
    }

    /**
     * Get sufixoCepPagador
     *
     * @return string 
     */
    public function getSufixoCepPagador()
    {
        return $this->sufixoCepPagador;
    }

    /**
     * Set sacadorSegundaMensagem
     *
     * @param string $sacadorSegundaMensagem
     * @return Remittance
     */
    public function setSacadorSegundaMensagem($sacadorSegundaMensagem)
    {
        $this->sacadorSegundaMensagem = $sacadorSegundaMensagem;

        return $this;
    }

    /**
     * Get sacadorSegundaMensagem
     *
     * @return string 
     */
    public function getSacadorSegundaMensagem()
    {
        return $this->sacadorSegundaMensagem;
    }

    /**
     * Set numeroRemessa
     *
     * @param string $numeroRemessa
     * @return Remittance
     */
    public function setNumeroRemessa($numeroRemessa)
    {
        $this->numeroRemessa = $numeroRemessa;

        return $this;
    }

    /**
     * Get numeroRemessa
     *
     * @return string 
     */
    public function getNumeroRemessa()
    {
        return $this->numeroRemessa;
    }

    /**
     * Set issueDate
     *
     * @param \DateTime $issueDate
     * @return Remittance
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
     * Set remittanceSequential
     *
     * @param integer $remittanceSequential
     * @return Remittance
     */
    public function setRemittanceSequential($remittanceSequential)
    {
        $this->remittanceSequential = $remittanceSequential;

        return $this;
    }

    /**
     * Get remittanceSequential
     *
     * @return integer 
     */
    public function getRemittanceSequential()
    {
        return $this->remittanceSequential;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Remittance
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
}
