<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ImportCardsTam
 *
 * @ORM\Table(name="import_cards_tam")
 * @ORM\Entity
 */
class ImportCardsTam
{
    /**
     * @var string
     *
     * @ORM\Column(name="cartao", type="string", length=200, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $cartao = '';

    /**
     * @var string
     *
     * @ORM\Column(name="cadastro", type="string", length=200, nullable=true)
     */
    private $cadastro;

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
     * @ORM\Column(name="numero_fidelidade", type="string", length=200, nullable=true)
     */
    private $numeroFidelidade;

    /**
     * @var string
     *
     * @ORM\Column(name="assinatura_eletronica", type="string", length=200, nullable=true)
     */
    private $assinaturaEletronica;

    /**
     * @var string
     *
     * @ORM\Column(name="senha_resgate", type="string", length=200, nullable=true)
     */
    private $senhaResgate;

    /**
     * @var string
     *
     * @ORM\Column(name="cpf", type="string", length=200, nullable=true)
     */
    private $cpf;

    /**
     * @var string
     *
     * @ORM\Column(name="senha_multiplus", type="string", length=200, nullable=true)
     */
    private $senhaMultiplus;

    /**
     * @var string
     *
     * @ORM\Column(name="vencimento", type="string", length=200, nullable=true)
     */
    private $vencimento;

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
     * @ORM\Column(name="total", type="string", length=200, nullable=true)
     */
    private $total;

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
     * @ORM\Column(name="token", type="string", length=20, nullable=false)
     */
    private $token;


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
     * Set cadastro
     *
     * @param string $cadastro
     * @return ImportCardsTam
     */
    public function setCadastro($cadastro)
    {
        $this->cadastro = $cadastro;

        return $this;
    }

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
     * @return ImportCardsTam
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
     * @return ImportCardsTam
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
     * Set numeroFidelidade
     *
     * @param string $numeroFidelidade
     * @return ImportCardsTam
     */
    public function setNumeroFidelidade($numeroFidelidade)
    {
        $this->numeroFidelidade = $numeroFidelidade;

        return $this;
    }

    /**
     * Get numeroFidelidade
     *
     * @return string 
     */
    public function getNumeroFidelidade()
    {
        return $this->numeroFidelidade;
    }

    /**
     * Set assinaturaEletronica
     *
     * @param string $assinaturaEletronica
     * @return ImportCardsTam
     */
    public function setAssinaturaEletronica($assinaturaEletronica)
    {
        $this->assinaturaEletronica = $assinaturaEletronica;

        return $this;
    }

    /**
     * Get assinaturaEletronica
     *
     * @return string 
     */
    public function getAssinaturaEletronica()
    {
        return $this->assinaturaEletronica;
    }

    /**
     * Set senhaResgate
     *
     * @param string $senhaResgate
     * @return ImportCardsTam
     */
    public function setSenhaResgate($senhaResgate)
    {
        $this->senhaResgate = $senhaResgate;

        return $this;
    }

    /**
     * Get senhaResgate
     *
     * @return string 
     */
    public function getSenhaResgate()
    {
        return $this->senhaResgate;
    }

    /**
     * Set cpf
     *
     * @param string $cpf
     * @return ImportCardsTam
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
     * Set senhaMultiplus
     *
     * @param string $senhaMultiplus
     * @return ImportCardsTam
     */
    public function setSenhaMultiplus($senhaMultiplus)
    {
        $this->senhaMultiplus = $senhaMultiplus;

        return $this;
    }

    /**
     * Get senhaMultiplus
     *
     * @return string 
     */
    public function getSenhaMultiplus()
    {
        return $this->senhaMultiplus;
    }

    /**
     * Set vencimento
     *
     * @param string $vencimento
     * @return ImportCardsTam
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
     * Set quantidadeMilhas
     *
     * @param string $quantidadeMilhas
     * @return ImportCardsTam
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
     * @return ImportCardsTam
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
     * Set total
     *
     * @param string $total
     * @return ImportCardsTam
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
     * Set valorPorMil
     *
     * @param string $valorPorMil
     * @return ImportCardsTam
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
     * @return ImportCardsTam
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
     * Set observacao
     *
     * @param string $observacao
     * @return ImportCardsTam
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
     * @return ImportCardsTam
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
     * @return ImportCardsTam
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
     * @return ImportCardsTam
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
     * Set token
     *
     * @param string $token
     * @return ImportCardsTam
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }
}
