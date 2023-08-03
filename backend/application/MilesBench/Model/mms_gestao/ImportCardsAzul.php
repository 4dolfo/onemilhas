<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ImportCardsAzul
 *
 * @ORM\Table(name="import_cards_azul")
 * @ORM\Entity
 */
class ImportCardsAzul
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
     * @ORM\Column(name="milhas_acumulo", type="string", length=200, nullable=true)
     */
    private $milhasAcumulo;

    /**
     * @var string
     *
     * @ORM\Column(name="total", type="string", length=200, nullable=true)
     */
    private $total;

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
     * @ORM\Column(name="observacao_2", type="string", length=200, nullable=true)
     */
    private $observacao2;


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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * Set milhasAcumulo
     *
     * @param string $milhasAcumulo
     * @return ImportCardsAzul
     */
    public function setMilhasAcumulo($milhasAcumulo)
    {
        $this->milhasAcumulo = $milhasAcumulo;

        return $this;
    }

    /**
     * Get milhasAcumulo
     *
     * @return string 
     */
    public function getMilhasAcumulo()
    {
        return $this->milhasAcumulo;
    }

    /**
     * Set total
     *
     * @param string $total
     * @return ImportCardsAzul
     */
    public function setTotal($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return string 
     */
    public function getTotal()
    {
        return $this->total;
    }

    /**
     * Set real
     *
     * @param string $real
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * @return ImportCardsAzul
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
     * Set observacao2
     *
     * @param string $observacao2
     * @return ImportCardsAzul
     */
    public function setObservacao2($observacao2)
    {
        $this->observacao2 = $observacao2;

        return $this;
    }

    /**
     * Get observacao2
     *
     * @return string 
     */
    public function getObservacao2()
    {
        return $this->observacao2;
    }
}
