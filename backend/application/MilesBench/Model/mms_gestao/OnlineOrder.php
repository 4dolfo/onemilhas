<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * OnlineOrder
 *
 * @ORM\Table(name="online_order", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})})
 * @ORM\Entity
 */
class OnlineOrder
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
     * @ORM\Column(name="external_id", type="string", length=45, nullable=true)
     */
    private $externalId;

    /**
     * @var string
     *
     * @ORM\Column(name="airline", type="string", length=45, nullable=false)
     */
    private $airline;

    /**
     * @var string
     *
     * @ORM\Column(name="miles_used", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $milesUsed = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="total_cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $totalCost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=45, nullable=false)
     */
    private $status = 'PENDENTE';

    /**
     * @var string
     *
     * @ORM\Column(name="client_email", type="string", length=200, nullable=false)
     */
    private $clientEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="client_name", type="string", length=200, nullable=false)
     */
    private $clientName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="comments", type="string", length=600, nullable=true)
     */
    private $comments;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="boarding_date", type="datetime", nullable=false)
     */
    private $boardingDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="landing_date", type="datetime", nullable=false)
     */
    private $landingDate;

    /**
     * @var string
     *
     * @ORM\Column(name="cancel_reason", type="string", length=100, nullable=true)
     */
    private $cancelReason;

    /**
     * @var string
     *
     * @ORM\Column(name="user_session", type="string", length=300, nullable=true)
     */
    private $userSession;

    /**
     * @var string
     *
     * @ORM\Column(name="commercial_status", type="string", length=5, nullable=true)
     */
    private $commercialStatus = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="marckup_cliente", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $marckupCliente = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="hash_code", type="string", length=155, nullable=true)
     */
    private $hashCode;

    /**
     * @var string
     *
     * @ORM\Column(name="client_login", type="string", length=26, nullable=true)
     */
    private $clientLogin;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_method", type="string", length=255, nullable=true)
     */
    private $paymentMethod;

    /**
     * @var float
     *
     * @ORM\Column(name="economy", type="float", precision=20, scale=2, nullable=true)
     */
    private $economy = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="emission_method", type="string", length=255, nullable=true)
     */
    private $emissionMethod;

    /**
     * @var string
     *
     * @ORM\Column(name="has_begun", type="string", length=5, nullable=true)
     */
    private $hasBegun = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="notificationURL", type="string", length=250, nullable=true)
     */
    private $notificationurl;

    /**
     * @var string
     *
     * @ORM\Column(name="notificationCode", type="string", length=250, nullable=true)
     */
    private $notificationcode;

    /**
     * @var string
     *
     * @ORM\Column(name="notificationType", type="string", length=250, nullable=true)
     */
    private $notificationtype;


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
     * Set externalId
     *
     * @param string $externalId
     * @return OnlineOrder
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;

        return $this;
    }

    /**
     * Get externalId
     *
     * @return string 
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * Set airline
     *
     * @param string $airline
     * @return OnlineOrder
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
     * Set milesUsed
     *
     * @param string $milesUsed
     * @return OnlineOrder
     */
    public function setMilesUsed($milesUsed)
    {
        $this->milesUsed = $milesUsed;

        return $this;
    }

    /**
     * Get milesUsed
     *
     * @return string 
     */
    public function getMilesUsed()
    {
        return $this->milesUsed;
    }

    /**
     * Set totalCost
     *
     * @param string $totalCost
     * @return OnlineOrder
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;

        return $this;
    }

    /**
     * Get totalCost
     *
     * @return string 
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return OnlineOrder
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
     * Set clientEmail
     *
     * @param string $clientEmail
     * @return OnlineOrder
     */
    public function setClientEmail($clientEmail)
    {
        $this->clientEmail = $clientEmail;

        return $this;
    }

    /**
     * Get clientEmail
     *
     * @return string 
     */
    public function getClientEmail()
    {
        return $this->clientEmail;
    }

    /**
     * Set clientName
     *
     * @param string $clientName
     * @return OnlineOrder
     */
    public function setClientName($clientName)
    {
        $this->clientName = $clientName;

        return $this;
    }

    /**
     * Get clientName
     *
     * @return string 
     */
    public function getClientName()
    {
        return $this->clientName;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return OnlineOrder
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set comments
     *
     * @param string $comments
     * @return OnlineOrder
     */
    public function setComments($comments)
    {
        $this->comments = $comments;

        return $this;
    }

    /**
     * Get comments
     *
     * @return string 
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Set boardingDate
     *
     * @param \DateTime $boardingDate
     * @return OnlineOrder
     */
    public function setBoardingDate($boardingDate)
    {
        $this->boardingDate = $boardingDate;

        return $this;
    }

    /**
     * Get boardingDate
     *
     * @return \DateTime 
     */
    public function getBoardingDate()
    {
        return $this->boardingDate;
    }

    /**
     * Set landingDate
     *
     * @param \DateTime $landingDate
     * @return OnlineOrder
     */
    public function setLandingDate($landingDate)
    {
        $this->landingDate = $landingDate;

        return $this;
    }

    /**
     * Get landingDate
     *
     * @return \DateTime 
     */
    public function getLandingDate()
    {
        return $this->landingDate;
    }

    /**
     * Set cancelReason
     *
     * @param string $cancelReason
     * @return OnlineOrder
     */
    public function setCancelReason($cancelReason)
    {
        $this->cancelReason = $cancelReason;

        return $this;
    }

    /**
     * Get cancelReason
     *
     * @return string 
     */
    public function getCancelReason()
    {
        return $this->cancelReason;
    }

    /**
     * Set userSession
     *
     * @param string $userSession
     * @return OnlineOrder
     */
    public function setUserSession($userSession)
    {
        $this->userSession = $userSession;

        return $this;
    }

    /**
     * Get userSession
     *
     * @return string 
     */
    public function getUserSession()
    {
        return $this->userSession;
    }

    /**
     * Set commercialStatus
     *
     * @param string $commercialStatus
     * @return OnlineOrder
     */
    public function setCommercialStatus($commercialStatus)
    {
        $this->commercialStatus = $commercialStatus;

        return $this;
    }

    /**
     * Get commercialStatus
     *
     * @return string 
     */
    public function getCommercialStatus()
    {
        return $this->commercialStatus;
    }

    /**
     * Set marckupCliente
     *
     * @param string $marckupCliente
     * @return OnlineOrder
     */
    public function setMarckupCliente($marckupCliente)
    {
        $this->marckupCliente = $marckupCliente;

        return $this;
    }

    /**
     * Get marckupCliente
     *
     * @return string 
     */
    public function getMarckupCliente()
    {
        return $this->marckupCliente;
    }

    /**
     * Set hashCode
     *
     * @param string $hashCode
     * @return OnlineOrder
     */
    public function setHashCode($hashCode)
    {
        $this->hashCode = $hashCode;

        return $this;
    }

    /**
     * Get hashCode
     *
     * @return string 
     */
    public function getHashCode()
    {
        return $this->hashCode;
    }

    /**
     * Set clientLogin
     *
     * @param string $clientLogin
     * @return OnlineOrder
     */
    public function setClientLogin($clientLogin)
    {
        $this->clientLogin = $clientLogin;

        return $this;
    }

    /**
     * Get clientLogin
     *
     * @return string 
     */
    public function getClientLogin()
    {
        return $this->clientLogin;
    }

    /**
     * Set paymentMethod
     *
     * @param string $paymentMethod
     * @return OnlineOrder
     */
    public function setPaymentMethod($paymentMethod)
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    /**
     * Get paymentMethod
     *
     * @return string 
     */
    public function getPaymentMethod()
    {
        return $this->paymentMethod;
    }

    /**
     * Set economy
     *
     * @param float $economy
     * @return OnlineOrder
     */
    public function setEconomy($economy)
    {
        $this->economy = $economy;

        return $this;
    }

    /**
     * Get economy
     *
     * @return float 
     */
    public function getEconomy()
    {
        return $this->economy;
    }

    /**
     * Set emissionMethod
     *
     * @param string $emissionMethod
     * @return OnlineOrder
     */
    public function setEmissionMethod($emissionMethod)
    {
        $this->emissionMethod = $emissionMethod;

        return $this;
    }

    /**
     * Get emissionMethod
     *
     * @return string 
     */
    public function getEmissionMethod()
    {
        return $this->emissionMethod;
    }

    /**
     * Set hasBegun
     *
     * @param string $hasBegun
     * @return OnlineOrder
     */
    public function setHasBegun($hasBegun)
    {
        $this->hasBegun = $hasBegun;

        return $this;
    }

    /**
     * Get hasBegun
     *
     * @return string 
     */
    public function getHasBegun()
    {
        return $this->hasBegun;
    }

    /**
     * Set notificationurl
     *
     * @param string $notificationurl
     * @return OnlineOrder
     */
    public function setNotificationurl($notificationurl)
    {
        $this->notificationurl = $notificationurl;

        return $this;
    }

    /**
     * Get notificationurl
     *
     * @return string 
     */
    public function getNotificationurl()
    {
        return $this->notificationurl;
    }

    /**
     * Set notificationcode
     *
     * @param string $notificationcode
     * @return OnlineOrder
     */
    public function setNotificationcode($notificationcode)
    {
        $this->notificationcode = $notificationcode;

        return $this;
    }

    /**
     * Get notificationcode
     *
     * @return string 
     */
    public function getNotificationcode()
    {
        return $this->notificationcode;
    }

    /**
     * Set notificationtype
     *
     * @param string $notificationtype
     * @return OnlineOrder
     */
    public function setNotificationtype($notificationtype)
    {
        $this->notificationtype = $notificationtype;

        return $this;
    }

    /**
     * Get notificationtype
     *
     * @return string 
     */
    public function getNotificationtype()
    {
        return $this->notificationtype;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="nome_pagador", type="string", length=255, nullable=true)
     */
    private $nomePagador;

    /**
     * @var string
     *
     * @ORM\Column(name="cpf_pagador", type="string", length=255, nullable=true)
     */
    private $cpfPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="endereco_pagador", type="string", length=255, nullable=true)
     */
    private $enderecoPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="numero_endereco_pagador", type="string", length=255, nullable=true)
     */
    private $numeroEnderecoPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="complemento_endereco_pagador", type="string", length=255, nullable=true)
     */
    private $complementoEnderecoPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="bairro_endereco_pagador", type="string", length=255, nullable=true)
     */
    private $bairroEnderecoPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="cidade_endereco_pagador", type="string", length=255, nullable=true)
     */
    private $cidadeEnderecoPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="estado_endereco_pagador", type="string", length=255, nullable=true)
     */
    private $estadoEnderecoPagador;

    /**
     * @var string
     *
     * @ORM\Column(name="cep_endereco_pagador", type="string", length=255, nullable=true)
     */
    private $cepEnderecoPagador;


    /**
     * Set nomePagador
     *
     * @param string $nomePagador
     * @return OnlineOrder
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
     * Set cpfPagador
     *
     * @param string $cpfPagador
     * @return OnlineOrder
     */
    public function setCpfPagador($cpfPagador)
    {
        $this->cpfPagador = $cpfPagador;

        return $this;
    }

    /**
     * Get cpfPagador
     *
     * @return string 
     */
    public function getCpfPagador()
    {
        return $this->cpfPagador;
    }

    /**
     * Set enderecoPagador
     *
     * @param string $enderecoPagador
     * @return OnlineOrder
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
     * Set numeroEnderecoPagador
     *
     * @param string $numeroEnderecoPagador
     * @return OnlineOrder
     */
    public function setNumeroEnderecoPagador($numeroEnderecoPagador)
    {
        $this->numeroEnderecoPagador = $numeroEnderecoPagador;

        return $this;
    }

    /**
     * Get numeroEnderecoPagador
     *
     * @return string 
     */
    public function getNumeroEnderecoPagador()
    {
        return $this->numeroEnderecoPagador;
    }

    /**
     * Set complementoEnderecoPagador
     *
     * @param string $complementoEnderecoPagador
     * @return OnlineOrder
     */
    public function setComplementoEnderecoPagador($complementoEnderecoPagador)
    {
        $this->complementoEnderecoPagador = $complementoEnderecoPagador;

        return $this;
    }

    /**
     * Get complementoEnderecoPagador
     *
     * @return string 
     */
    public function getComplementoEnderecoPagador()
    {
        return $this->complementoEnderecoPagador;
    }

    /**
     * Set bairroEnderecoPagador
     *
     * @param string $bairroEnderecoPagador
     * @return OnlineOrder
     */
    public function setBairroEnderecoPagador($bairroEnderecoPagador)
    {
        $this->bairroEnderecoPagador = $bairroEnderecoPagador;

        return $this;
    }

    /**
     * Get bairroEnderecoPagador
     *
     * @return string 
     */
    public function getBairroEnderecoPagador()
    {
        return $this->bairroEnderecoPagador;
    }

    /**
     * Set cidadeEnderecoPagador
     *
     * @param string $cidadeEnderecoPagador
     * @return OnlineOrder
     */
    public function setCidadeEnderecoPagador($cidadeEnderecoPagador)
    {
        $this->cidadeEnderecoPagador = $cidadeEnderecoPagador;

        return $this;
    }

    /**
     * Get cidadeEnderecoPagador
     *
     * @return string 
     */
    public function getCidadeEnderecoPagador()
    {
        return $this->cidadeEnderecoPagador;
    }

    /**
     * Set estadoEnderecoPagador
     *
     * @param string $estadoEnderecoPagador
     * @return OnlineOrder
     */
    public function setEstadoEnderecoPagador($estadoEnderecoPagador)
    {
        $this->estadoEnderecoPagador = $estadoEnderecoPagador;

        return $this;
    }

    /**
     * Get estadoEnderecoPagador
     *
     * @return string 
     */
    public function getEstadoEnderecoPagador()
    {
        return $this->estadoEnderecoPagador;
    }

    /**
     * Set cepEnderecoPagador
     *
     * @param string $cepEnderecoPagador
     * @return OnlineOrder
     */
    public function setCepEnderecoPagador($cepEnderecoPagador)
    {
        $this->cepEnderecoPagador = $cepEnderecoPagador;

        return $this;
    }

    /**
     * Get cepEnderecoPagador
     *
     * @return string 
     */
    public function getCepEnderecoPagador()
    {
        return $this->cepEnderecoPagador;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="client_phone", type="string", length=200, nullable=true)
     */
    private $clientPhone;


    /**
     * Set clientPhone
     *
     * @param string $clientPhone
     * @return OnlineOrder
     */
    public function setClientPhone($clientPhone)
    {
        $this->clientPhone = $clientPhone;

        return $this;
    }

    /**
     * Get clientPhone
     *
     * @return string 
     */
    public function getClientPhone()
    {
        return $this->clientPhone;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="discounts", type="float", precision=20, scale=2, nullable=true)
     */
    private $discounts = '0.00';


    /**
     * Set discounts
     *
     * @param float $discounts
     * @return OnlineOrder
     */
    public function setDiscounts($discounts)
    {
        $this->discounts = $discounts;

        return $this;
    }

    /**
     * Get discounts
     *
     * @return float 
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="nfeId", type="string", length=255, nullable=true)
     */
    private $nfeid;


    /**
     * Set nfeid
     *
     * @param string $nfeid
     * @return OnlineOrder
     */
    public function setNfeid($nfeid)
    {
        $this->nfeid = $nfeid;

        return $this;
    }

    /**
     * Get nfeid
     *
     * @return string 
     */
    public function getNfeid()
    {
        return $this->nfeid;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="tax_payment", type="float", precision=20, scale=2, nullable=true)
     */
    private $taxPayment = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="tax_approval", type="float", precision=20, scale=2, nullable=true)
     */
    private $taxApproval = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="value_payment", type="float", precision=20, scale=2, nullable=true)
     */
    private $valuePayment = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="value_approval", type="float", precision=20, scale=2, nullable=true)
     */
    private $valueApproval = '0.00';


    /**
     * Set taxPayment
     *
     * @param float $taxPayment
     * @return OnlineOrder
     */
    public function setTaxPayment($taxPayment)
    {
        $this->taxPayment = $taxPayment;

        return $this;
    }

    /**
     * Get taxPayment
     *
     * @return float 
     */
    public function getTaxPayment()
    {
        return $this->taxPayment;
    }

    /**
     * Set taxApproval
     *
     * @param float $taxApproval
     * @return OnlineOrder
     */
    public function setTaxApproval($taxApproval)
    {
        $this->taxApproval = $taxApproval;

        return $this;
    }

    /**
     * Get taxApproval
     *
     * @return float 
     */
    public function getTaxApproval()
    {
        return $this->taxApproval;
    }

    /**
     * Set valuePayment
     *
     * @param float $valuePayment
     * @return OnlineOrder
     */
    public function setValuePayment($valuePayment)
    {
        $this->valuePayment = $valuePayment;

        return $this;
    }

    /**
     * Get valuePayment
     *
     * @return float 
     */
    public function getValuePayment()
    {
        return $this->valuePayment;
    }

    /**
     * Set valueApproval
     *
     * @param float $valueApproval
     * @return OnlineOrder
     */
    public function setValueApproval($valueApproval)
    {
        $this->valueApproval = $valueApproval;

        return $this;
    }

    /**
     * Get valueApproval
     *
     * @return float 
     */
    public function getValueApproval()
    {
        return $this->valueApproval;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="original_system", type="string", length=20, nullable=true)
     */
    private $originalSystem;


    /**
     * Set originalSystem
     *
     * @param string $originalSystem
     * @return OnlineOrder
     */
    public function setOriginalSystem($originalSystem)
    {
        $this->originalSystem = $originalSystem;

        return $this;
    }

    /**
     * Get originalSystem
     *
     * @return string 
     */
    public function getOriginalSystem()
    {
        return $this->originalSystem;
    }
    /**
     * @var integer
     *
     * @ORM\Column(name="emissionMethodCompany", type="integer", nullable=true)
     */
    private $emissionmethodcompany;

    /**
     * @var integer
     *
     * @ORM\Column(name="emissionMethodMiles", type="integer", nullable=true)
     */
    private $emissionmethodmiles;


    /**
     * Set emissionmethodcompany
     *
     * @param integer $emissionmethodcompany
     * @return OnlineOrder
     */
    public function setEmissionmethodcompany($emissionmethodcompany)
    {
        $this->emissionmethodcompany = $emissionmethodcompany;

        return $this;
    }

    /**
     * Get emissionmethodcompany
     *
     * @return integer 
     */
    public function getEmissionmethodcompany()
    {
        return $this->emissionmethodcompany;
    }

    /**
     * Set emissionmethodmiles
     *
     * @param integer $emissionmethodmiles
     * @return OnlineOrder
     */
    public function setEmissionmethodmiles($emissionmethodmiles)
    {
        $this->emissionmethodmiles = $emissionmethodmiles;

        return $this;
    }

    /**
     * Get emissionmethodmiles
     *
     * @return integer 
     */
    public function getEmissionmethodmiles()
    {
        return $this->emissionmethodmiles;
    }
    /**
     * @var integer
     *
     * @ORM\Column(name="idCompany", type="integer", nullable=true)
     */
    private $idcompany;

    /**
     * @var integer
     *
     * @ORM\Column(name="idMiles", type="integer", nullable=true)
     */
    private $idmiles;


    /**
     * Set idcompany
     *
     * @param integer $idcompany
     * @return OnlineOrder
     */
    public function setIdcompany($idcompany)
    {
        $this->idcompany = $idcompany;

        return $this;
    }

    /**
     * Get idcompany
     *
     * @return integer 
     */
    public function getIdcompany()
    {
        return $this->idcompany;
    }

    /**
     * Set idmiles
     *
     * @param integer $idmiles
     * @return OnlineOrder
     */
    public function setIdmiles($idmiles)
    {
        $this->idmiles = $idmiles;

        return $this;
    }

    /**
     * Get idmiles
     *
     * @return integer 
     */
    public function getIdmiles()
    {
        return $this->idmiles;
    }
    /**
     * @var integer
     *
     * @ORM\Column(name="agencia_id", type="integer", nullable=true)
     */
    private $agenciaId;


    /**
     * Set agenciaId
     *
     * @param integer $agenciaId
     * @return OnlineOrder
     */
    public function setAgenciaId($agenciaId)
    {
        $this->agenciaId = $agenciaId;

        return $this;
    }

    /**
     * Get agenciaId
     *
     * @return integer 
     */
    public function getAgenciaId()
    {
        return $this->agenciaId;
    }
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="first_boarding_date", type="datetime", nullable=true)
     */
    private $firstBoardingDate;


    /**
     * Set firstBoardingDate
     *
     * @param \DateTime $firstBoardingDate
     * @return OnlineOrder
     */
    public function setFirstBoardingDate($firstBoardingDate)
    {
        $this->firstBoardingDate = $firstBoardingDate;

        return $this;
    }

    /**
     * Get firstBoardingDate
     *
     * @return \DateTime 
     */
    public function getFirstBoardingDate()
    {
        return $this->firstBoardingDate;
    }
    /**
     * @var string
     *

     * @ORM\Column(name="order_post", type="string", length=10000, nullable=false)
     */
    private $orderPost;


    /**
     * Set orderPost
     *
     * @param string $orderPost
     * @return OnlineOrder
     */
    public function setOrderPost($orderPost)
    {
        $this->orderPost = $orderPost;

        return $this;
    }

    /**
     * Get orderPost
     *
     * @return string 
     */
    public function getOrderPost()
    {
        return $this->orderPost;
    }
    /**
     * @var boolean
     *
     * @ORM\Column(name="priority", type="boolean", nullable=true)
     */
    private $priority;


    /**
     * Set priority
     *
     * @param boolean $priority
     * @return OnlineOrder
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;
      
       return $this;
    }
    /**
     * Get priority
     *
     * @return boolean 
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="utm", type="string", length=1024, nullable=true)
     */
    private $utm;


    /**
     * Set utm
     *
     * @param string $utm
     * @return OnlineOrder
     */
    public function setUtm($utm)
    {
        $this->utm = $utm;
        return $this;
    }
    /**
     * Get utm
     *
     * @return string 
     */
    public function getUtm()
    {
        return $this->utm;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="cupom", type="string", length=144, nullable=true)
     */
    private $cupom;

    /**
     * @var string
     *
     * @ORM\Column(name="tipoCupom", type="string", length=144, nullable=true)
     */
    private $tipocupom;

    /**
     * @var string
     *
     * @ORM\Column(name="indicacao", type="string", length=144, nullable=true)
     */
    private $indicacao;

    /**
     * @var float
     *
     * @ORM\Column(name="credito_usado", type="float", precision=20, scale=2, nullable=true)
     */
    private $creditoUsado = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="valorCupom", type="float", precision=20, scale=2, nullable=true)
     */
    private $valorcupom = '0.00';


    /**
     * Set cupom
     *
     * @param string $cupom
     * @return OnlineOrder
     */
    public function setCupom($cupom)
    {
        $this->cupom = $cupom;

        return $this;
    }

    /**
     * Get cupom
     *
     * @return string 
     */
    public function getCupom()
    {
        return $this->cupom;
    }

    /**
     * Set tipocupom
     *
     * @param string $tipocupom
     * @return OnlineOrder
     */
    public function setTipocupom($tipocupom)
    {
        $this->tipocupom = $tipocupom;

        return $this;
    }

    /**
     * Get tipocupom
     *
     * @return string 
     */
    public function getTipocupom()
    {
        return $this->tipocupom;
    }

    /**
     * Set indicacao
     *
     * @param string $indicacao
     * @return OnlineOrder
     */
    public function setIndicacao($indicacao)
    {
        $this->indicacao = $indicacao;

        return $this;
    }

    /**
     * Get indicacao
     *
     * @return string 
     */
    public function getIndicacao()
    {
        return $this->indicacao;
    }

    /**
     * Set creditoUsado
     *
     * @param float $creditoUsado
     * @return OnlineOrder
     */
    public function setCreditoUsado($creditoUsado)
    {
        $this->creditoUsado = $creditoUsado;

        return $this;
    }

    /**
     * Get creditoUsado
     *
     * @return float 
     */
    public function getCreditoUsado()
    {
        return $this->creditoUsado;
    }

    /**
     * Set valorcupom
     *
     * @param float $valorcupom
     * @return OnlineOrder
     */
    public function setValorcupom($valorcupom)
    {
        $this->valorcupom = $valorcupom;

        return $this;
    }

    /**
     * Get valorcupom
     *
     * @return float 
     */
    public function getValorcupom()
    {
        return $this->valorcupom;
    }
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="data_transferencia", type="datetime", nullable=true)
     */
    private $dataTransferencia;


    /**
     * Set dataTransferencia
     *
     * @param \DateTime $dataTransferencia
     * @return OnlineOrder
     */
    public function setDataTransferencia($dataTransferencia)
    {
        $this->dataTransferencia = $dataTransferencia;

        return $this;
    }

    /**
     * Get dataTransferencia
     *
     * @return \DateTime 
     */
    public function getDataTransferencia()
    {
        return $this->dataTransferencia;
    }
    /**
     * @var integer
     *
     * @ORM\Column(name="payment_days", type="integer", nullable=true)
     */
    private $paymentDays = '0';


    /**
     * Set paymentDays
     *
     * @param integer $paymentDays
     * @return OnlineOrder
     */
    public function setPaymentDays($paymentDays)
    {
        $this->paymentDays = $paymentDays;

        return $this;
    }

    /**
     * Get paymentDays
     *
     * @return integer 
     */
    public function getPaymentDays()
    {
        return $this->paymentDays;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="total_parcelas", type="string", length=20, nullable=true)
     */
    private $totalParcelas;

    /**
     * @var float
     *
     * @ORM\Column(name="acrescimo", type="float", precision=20, scale=2, nullable=true)
     */
    private $acrescimo = '0.00';


    /**
     * Set totalParcelas
     *
     * @param string $totalParcelas
     * @return OnlineOrder
     */
    public function setTotalParcelas($totalParcelas)
    {
        $this->totalParcelas = $totalParcelas;

        return $this;
    }

    /**
     * Get totalParcelas
     *
     * @return string 
     */
    public function getTotalParcelas()
    {
        return $this->totalParcelas;
    }

    /**
     * Set acrescimo
     *
     * @param float $acrescimo
     * @return OnlineOrder
     */
    public function setAcrescimo($acrescimo)
    {
        $this->acrescimo = $acrescimo;

        return $this;
    }

    /**
     * Get acrescimo
     *
     * @return float 
     */
    public function getAcrescimo()
    {
        return $this->acrescimo;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="comprovanteTransferencia", type="string", length=3024, nullable=true)
     */
    private $comprovantetransferencia;


    /**
     * Set comprovantetransferencia
     *
     * @param string $comprovantetransferencia
     * @return OnlineOrder
     */
    public function setComprovantetransferencia($comprovantetransferencia)
    {
        $this->comprovantetransferencia = $comprovantetransferencia;

        return $this;
    }

    /**
     * Get comprovantetransferencia
     *
     * @return string 
     */
    public function getComprovantetransferencia()
    {
        return $this->comprovantetransferencia;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="totalReal", type="float", precision=20, scale=2, nullable=true)
     */
    private $totalreal = '0.00';


    /**
     * Set totalreal
     *
     * @param float $totalreal
     * @return OnlineOrder
     */
    public function setTotalreal($totalreal)
    {
        $this->totalreal = $totalreal;

        return $this;
    }

    /**
     * Get totalreal
     *
     * @return float 
     */
    public function getTotalreal()
    {
        return $this->totalreal;
    }
}
