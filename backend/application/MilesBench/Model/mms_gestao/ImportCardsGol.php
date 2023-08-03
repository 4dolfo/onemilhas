<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ImportCardsGol
 *
 * @ORM\Table(name="import_cards_gol")
 * @ORM\Entity
 */
class ImportCardsGol
{
    /**
     * @var string
     *
     * @ORM\Column(name="cadastro", type="string", length=200, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $cadastro = '';

    /**
     * @var string
     *
     * @ORM\Column(name="data_compra", type="string", length=200, nullable=true)
     */
    private $dataCompra;

    /**
     * @var string
     *
     * @ORM\Column(name="cliente", type="string", length=200, nullable=true)
     */
    private $cliente;

    /**
     * @var string
     *
     * @ORM\Column(name="numero_cartao", type="string", length=200, nullable=true)
     */
    private $numeroCartao;

    /**
     * @var string
     *
     * @ORM\Column(name="senha", type="string", length=200, nullable=true)
     */
    private $senha;

    /**
     * @var string
     *
     * @ORM\Column(name="cpf", type="string", length=200, nullable=true)
     */
    private $cpf;

    /**
     * @var string
     *
     * @ORM\Column(name="quantidade_milhas", type="string", length=200, nullable=true)
     */
    private $quantidadeMilhas;

    /**
     * @var string
     *
     * @ORM\Column(name="milhas_utilizadas", type="string", length=200, nullable=true)
     */
    private $milhasUtilizadas;

    /**
     * @var string
     *
     * @ORM\Column(name="real", type="string", length=200, nullable=true)
     */
    private $real;

    /**
     * @var string
     *
     * @ORM\Column(name="valor_por_mil", type="string", length=200, nullable=true)
     */
    private $valorPorMil;

    /**
     * @var string
     *
     * @ORM\Column(name="valor_total", type="string", length=200, nullable=true)
     */
    private $valorTotal;

    /**
     * @var string
     *
     * @ORM\Column(name="situacao_analise", type="string", length=200, nullable=true)
     */
    private $situacaoAnalise;

    /**
     * @var string
     *
     * @ORM\Column(name="observacao", type="string", length=200, nullable=true)
     */
    private $observacao;

    /**
     * @var string
     *
     * @ORM\Column(name="telefone_contato", type="string", length=200, nullable=true)
     */
    private $telefoneContato;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=200, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="pagamento", type="string", length=200, nullable=true)
     */
    private $pagamento;

    /**
     * @var string
     *
     * @ORM\Column(name="expirar", type="string", length=200, nullable=true)
     */
    private $expirar;


    /**
     * Get cadastro
     *
     * @return string 
     */
    public function getCadastro()
    {
        return $this->cadastro;
    }

    /**
     * Set dataCompra
     *
     * @param string $dataCompra
     * @return ImportCardsGol
     */
    public function setDataCompra($dataCompra)
    {
        $this->dataCompra = $dataCompra;

        return $this;
    }

    /**
     * Get dataCompra
     *
     * @return string 
     */
    public function getDataCompra()
    {
        return $this->dataCompra;
    }

    /**
     * Set cliente
     *
     * @param string $cliente
     * @return ImportCardsGol
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
     * Set numeroCartao
     *
     * @param string $numeroCartao
     * @return ImportCardsGol
     */
    public function setNumeroCartao($numeroCartao)
    {
        $this->numeroCartao = $numeroCartao;

        return $this;
    }

    /**
     * Get numeroCartao
     *
     * @return string 
     */
    public function getNumeroCartao()
    {
        return $this->numeroCartao;
    }

    /**
     * Set senha
     *
     * @param string $senha
     * @return ImportCardsGol
     */
    public function setSenha($senha)
    {
        $this->senha = $senha;

        return $this;
    }

    /**
     * Get senha
     *
     * @return string 
     */
    public function getSenha()
    {
        return $this->senha;
    }

    /**
     * Set cpf
     *
     * @param string $cpf
     * @return ImportCardsGol
     */
    public function setCpf($cpf)
    {
        $this->cpf = $cpf;

        return $this;
    }

    /**
     * Get cpf
     *
     * @return string 
     */
    public function getCpf()
    {
        return $this->cpf;
    }

    /**
     * Set quantidadeMilhas
     *
     * @param string $quantidadeMilhas
     * @return ImportCardsGol
     */
    public function setQuantidadeMilhas($quantidadeMilhas)
    {
        $this->quantidadeMilhas = $quantidadeMilhas;

        return $this;
    }

    /**
     * Get quantidadeMilhas
     *
     * @return string 
     */
    public function getQuantidadeMilhas()
    {
        return $this->quantidadeMilhas;
    }

    /**
     * Set milhasUtilizadas
     *
     * @param string $milhasUtilizadas
     * @return ImportCardsGol
     */
    public function setMilhasUtilizadas($milhasUtilizadas)
    {
        $this->milhasUtilizadas = $milhasUtilizadas;

        return $this;
    }

    /**
     * Get milhasUtilizadas
     *
     * @return string 
     */
    public function getMilhasUtilizadas()
    {
        return $this->milhasUtilizadas;
    }

    /**
     * Set real
     *
     * @param string $real
     * @return ImportCardsGol
     */
    public function setReal($real)
    {
        $this->real = $real;

        return $this;
    }

    /**
     * Get real
     *
     * @return string 
     */
    public function getReal()
    {
        return $this->real;
    }

    /**
     * Set valorPorMil
     *
     * @param string $valorPorMil
     * @return ImportCardsGol
     */
    public function setValorPorMil($valorPorMil)
    {
        $this->valorPorMil = $valorPorMil;

        return $this;
    }

    /**
     * Get valorPorMil
     *
     * @return string 
     */
    public function getValorPorMil()
    {
        return $this->valorPorMil;
    }

    /**
     * Set valorTotal
     *
     * @param string $valorTotal
     * @return ImportCardsGol
     */
    public function setValorTotal($valorTotal)
    {
        $this->valorTotal = $valorTotal;

        return $this;
    }

    /**
     * Get valorTotal
     *
     * @return string 
     */
    public function getValorTotal()
    {
        return $this->valorTotal;
    }

    /**
     * Set situacaoAnalise
     *
     * @param string $situacaoAnalise
     * @return ImportCardsGol
     */
    public function setSituacaoAnalise($situacaoAnalise)
    {
        $this->situacaoAnalise = $situacaoAnalise;

        return $this;
    }

    /**
     * Get situacaoAnalise
     *
     * @return string 
     */
    public function getSituacaoAnalise()
    {
        return $this->situacaoAnalise;
    }

    /**
     * Set observacao
     *
     * @param string $observacao
     * @return ImportCardsGol
     */
    public function setObservacao($observacao)
    {
        $this->observacao = $observacao;

        return $this;
    }

    /**
     * Get observacao
     *
     * @return string 
     */
    public function getObservacao()
    {
        return $this->observacao;
    }

    /**
     * Set telefoneContato
     *
     * @param string $telefoneContato
     * @return ImportCardsGol
     */
    public function setTelefoneContato($telefoneContato)
    {
        $this->telefoneContato = $telefoneContato;

        return $this;
    }

    /**
     * Get telefoneContato
     *
     * @return string 
     */
    public function getTelefoneContato()
    {
        return $this->telefoneContato;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return ImportCardsGol
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
     * Set pagamento
     *
     * @param string $pagamento
     * @return ImportCardsGol
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
     * Set expirar
     *
     * @param string $expirar
     * @return ImportCardsGol
     */
    public function setExpirar($expirar)
    {
        $this->expirar = $expirar;

        return $this;
    }

    /**
     * Get expirar
     *
     * @return string 
     */
    public function getExpirar()
    {
        return $this->expirar;
    }
}
