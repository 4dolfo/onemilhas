<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Businesspartner
 *
 * @ORM\Table(name="businesspartner", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"}), @ORM\UniqueConstraint(name="number_UNIQUE", columns={"registration_code", "name", "partner_type"})}, indexes={@ORM\Index(name="city_idx", columns={"city_id"}), @ORM\Index(name="client_id", columns={"client_id"}), @ORM\Index(name="client_id_2", columns={"client_id"}), @ORM\Index(name="plan_id", columns={"plan_id"}), @ORM\Index(name="operation_plan_id", columns={"operation_plan_id"}), @ORM\Index(name="city_finnancial_id", columns={"city_finnancial_id"}), @ORM\Index(name="dealer", columns={"dealer"}), @ORM\Index(name="master_client", columns={"master_client"})})
 * @ORM\Entity
 */
class Businesspartner
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
     * @ORM\Column(name="name", type="string", length=150, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_code", type="string", length=45, nullable=true)
     */
    private $registrationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="adress", type="string", length=200, nullable=true)
     */
    private $adress;

    /**
     * @var string
     *
     * @ORM\Column(name="partner_type", type="string", length=10, nullable=false)
     */
    private $partnerType = 'P';

    /**
     * @var string
     *
     * @ORM\Column(name="acess_name", type="string", length=45, nullable=true)
     */
    private $acessName;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=200, nullable=true)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="password", type="string", length=45, nullable=true)
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number", type="string", length=70, nullable=true)
     */
    private $phoneNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number2", type="string", length=80, nullable=true)
     */
    private $phoneNumber2;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number3", type="string", length=80, nullable=true)
     */
    private $phoneNumber3;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=45, nullable=true)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthdate", type="datetime", nullable=true)
     */
    private $birthdate;

    /**
     * @var string
     *
     * @ORM\Column(name="bank", type="string", length=45, nullable=true)
     */
    private $bank;

    /**
     * @var string
     *
     * @ORM\Column(name="agency", type="string", length=45, nullable=true)
     */
    private $agency;

    /**
     * @var string
     *
     * @ORM\Column(name="account", type="string", length=45, nullable=true)
     */
    private $account;

    /**
     * @var string
     *
     * @ORM\Column(name="block_reason", type="string", length=200, nullable=true)
     */
    private $blockReason;

    /**
     * @var string
     *
     * @ORM\Column(name="is_master", type="string", length=20, nullable=true)
     */
    private $isMaster;

    /**
     * @var string
     *
     * @ORM\Column(name="webservice_login", type="string", length=1, nullable=true)
     */
    private $webserviceLogin;

    /**
     * @var integer
     *
     * @ORM\Column(name="client_id", type="integer", nullable=true)
     */
    private $clientId;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_type", type="string", length=30, nullable=true)
     */
    private $paymentType;

    /**
     * @var integer
     *
     * @ORM\Column(name="payment_days", type="integer", nullable=true)
     */
    private $paymentDays;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=500, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_analysis", type="string", length=6, nullable=true)
     */
    private $creditAnalysis;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_code_check", type="string", length=3, nullable=true)
     */
    private $registrationCodeCheck;

    /**
     * @var string
     *
     * @ORM\Column(name="adress_check", type="string", length=3, nullable=true)
     */
    private $adressCheck;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_description", type="string", length=200, nullable=true)
     */
    private $creditDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="company_name", type="string", length=200, nullable=true)
     */
    private $companyName;

    /**
     * @var float
     *
     * @ORM\Column(name="partner_limit", type="float", precision=20, scale=2, nullable=true)
     */
    private $partnerLimit = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="master_code", type="string", length=10, nullable=true)
     */
    private $masterCode;

    /**
     * @var string
     *
     * @ORM\Column(name="working_days", type="string", length=5, nullable=true)
     */
    private $workingDays = 'false';

    /**
     * @var integer
     *
     * @ORM\Column(name="second_payment_days", type="integer", nullable=true)
     */
    private $secondPaymentDays;

    /**
     * @var string
     *
     * @ORM\Column(name="second_working_days", type="string", length=5, nullable=true)
     */
    private $secondWorkingDays = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="billing_period", type="string", length=15, nullable=true)
     */
    private $billingPeriod;

    /**
     * @var string
     *
     * @ORM\Column(name="type_society", type="string", length=200, nullable=true)
     */
    private $typeSociety;

    /**
     * @var string
     *
     * @ORM\Column(name="mulct", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $mulct = '0.00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="register_date", type="datetime", nullable=true)
     */
    private $registerDate;

    /**
     * @var string
     *
     * @ORM\Column(name="adress_number", type="string", length=10, nullable=true)
     */
    private $adressNumber = '';

    /**
     * @var string
     *
     * @ORM\Column(name="adress_complement", type="string", length=16, nullable=true)
     */
    private $adressComplement = '';

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code", type="string", length=16, nullable=true)
     */
    private $zipCode = '';

    /**
     * @var string
     *
     * @ORM\Column(name="adress_district", type="string", length=26, nullable=true)
     */
    private $adressDistrict = '';

    /**
     * @var string
     *
     * @ORM\Column(name="commission", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $commission = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="docs", type="string", length=50, nullable=true)
     */
    private $docs;

    /**
     * @var string
     *
     * @ORM\Column(name="phone_number_airline", type="string", length=70, nullable=true)
     */
    private $phoneNumberAirline;

    /**
     * @var string
     *
     * @ORM\Column(name="cel_number_airline", type="string", length=70, nullable=true)
     */
    private $celNumberAirline;

    /**
     * @var string
     *
     * @ORM\Column(name="financial_contact", type="string", length=50, nullable=true)
     */
    private $financialContact;

    /**
     * @var string
     *
     * @ORM\Column(name="name_mother", type="string", length=75, nullable=true)
     */
    private $nameMother;

    /**
     * @var integer
     *
     * @ORM\Column(name="limit_margin", type="integer", nullable=false)
     */
    private $limitMargin = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="finnancial_email", type="string", length=250, nullable=true)
     */
    private $finnancialEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="interest", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $interest = '0.00';

    /**
     * @var integer
     *
     * @ORM\Column(name="days_to_boarding", type="integer", nullable=true)
     */
    private $daysToBoarding;

    /**
     * @var string
     *
     * @ORM\Column(name="adress_finnancial", type="string", length=200, nullable=true)
     */
    private $adressFinnancial;

    /**
     * @var string
     *
     * @ORM\Column(name="adress_complement_finnancial", type="string", length=16, nullable=true)
     */
    private $adressComplementFinnancial;

    /**
     * @var string
     *
     * @ORM\Column(name="adress_district_finnancial", type="string", length=26, nullable=true)
     */
    private $adressDistrictFinnancial;

    /**
     * @var string
     *
     * @ORM\Column(name="adress_number_finnancial", type="string", length=10, nullable=true)
     */
    private $adressNumberFinnancial;

    /**
     * @var string
     *
     * @ORM\Column(name="zip_code_finnancial", type="string", length=16, nullable=true)
     */
    private $zipCodeFinnancial;

    /**
     * @var string
     *
     * @ORM\Column(name="contact", type="string", length=255, nullable=true)
     */
    private $contact = '';

    /**
     * @var string
     *
     * @ORM\Column(name="use_commission", type="string", length=5, nullable=true)
     */
    private $useCommission = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="sub_client", type="string", length=5, nullable=true)
     */
    private $subClient = 'false';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_password_date", type="datetime", nullable=true)
     */
    private $lastPasswordDate;

    /**
     * @var string
     *
     * @ORM\Column(name="avoid_daily_reminder", type="string", length=5, nullable=true)
     */
    private $avoidDailyReminder = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="avoid_second_way_billets", type="string", length=5, nullable=true)
     */
    private $avoidSecondWayBillets = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="origin", type="string", length=3, nullable=true)
     */
    private $origin;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_slip_social_name", type="string", length=5, nullable=true)
     */
    private $bankSlipSocialName = 'false';

    /**
     * @var \SalePlans
     *
     * @ORM\ManyToOne(targetEntity="SalePlans")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plan_id", referencedColumnName="id")
     * })
     */
    private $plan;

    /**
     * @var \City
     *
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_id", referencedColumnName="id")
     * })
     */
    private $city;

    /**
     * @var \AirlineOperationsPlan
     *
     * @ORM\ManyToOne(targetEntity="AirlineOperationsPlan")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="operation_plan_id", referencedColumnName="id")
     * })
     */
    private $operationPlan;

    /**
     * @var \City
     *
     * @ORM\ManyToOne(targetEntity="City")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="city_finnancial_id", referencedColumnName="id")
     * })
     */
    private $cityFinnancial;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dealer", referencedColumnName="id")
     * })
     */
    private $dealer;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="master_client", referencedColumnName="id")
     * })
     */
    private $masterClient;


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
     * Set name
     *
     * @param string $name
     * @return Businesspartner
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set registrationCode
     *
     * @param string $registrationCode
     * @return Businesspartner
     */
    public function setRegistrationCode($registrationCode)
    {
        $this->registrationCode = $registrationCode;

        return $this;
    }

    /**
     * Get registrationCode
     *
     * @return string 
     */
    public function getRegistrationCode()
    {
        return $this->registrationCode;
    }

    /**
     * Set adress
     *
     * @param string $adress
     * @return Businesspartner
     */
    public function setAdress($adress)
    {
        $this->adress = $adress;

        return $this;
    }

    /**
     * Get adress
     *
     * @return string 
     */
    public function getAdress()
    {
        return $this->adress;
    }

    /**
     * Set partnerType
     *
     * @param string $partnerType
     * @return Businesspartner
     */
    public function setPartnerType($partnerType)
    {
        $this->partnerType = $partnerType;

        return $this;
    }

    /**
     * Get partnerType
     *
     * @return string 
     */
    public function getPartnerType()
    {
        return $this->partnerType;
    }

    /**
     * Set acessName
     *
     * @param string $acessName
     * @return Businesspartner
     */
    public function setAcessName($acessName)
    {
        $this->acessName = $acessName;

        return $this;
    }

    /**
     * Get acessName
     *
     * @return string 
     */
    public function getAcessName()
    {
        return $this->acessName;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Businesspartner
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
     * Set password
     *
     * @param string $password
     * @return Businesspartner
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string 
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set phoneNumber
     *
     * @param string $phoneNumber
     * @return Businesspartner
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    /**
     * Get phoneNumber
     *
     * @return string 
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * Set phoneNumber2
     *
     * @param string $phoneNumber2
     * @return Businesspartner
     */
    public function setPhoneNumber2($phoneNumber2)
    {
        $this->phoneNumber2 = $phoneNumber2;

        return $this;
    }

    /**
     * Get phoneNumber2
     *
     * @return string 
     */
    public function getPhoneNumber2()
    {
        return $this->phoneNumber2;
    }

    /**
     * Set phoneNumber3
     *
     * @param string $phoneNumber3
     * @return Businesspartner
     */
    public function setPhoneNumber3($phoneNumber3)
    {
        $this->phoneNumber3 = $phoneNumber3;

        return $this;
    }

    /**
     * Get phoneNumber3
     *
     * @return string 
     */
    public function getPhoneNumber3()
    {
        return $this->phoneNumber3;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Businesspartner
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
     * Set birthdate
     *
     * @param \DateTime $birthdate
     * @return Businesspartner
     */
    public function setBirthdate($birthdate)
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    /**
     * Get birthdate
     *
     * @return \DateTime 
     */
    public function getBirthdate()
    {
        return $this->birthdate;
    }

    /**
     * Set bank
     *
     * @param string $bank
     * @return Businesspartner
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * Get bank
     *
     * @return string 
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * Set agency
     *
     * @param string $agency
     * @return Businesspartner
     */
    public function setAgency($agency)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return string 
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Set account
     *
     * @param string $account
     * @return Businesspartner
     */
    public function setAccount($account)
    {
        $this->account = $account;

        return $this;
    }

    /**
     * Get account
     *
     * @return string 
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * Set blockReason
     *
     * @param string $blockReason
     * @return Businesspartner
     */
    public function setBlockReason($blockReason)
    {
        $this->blockReason = $blockReason;

        return $this;
    }

    /**
     * Get blockReason
     *
     * @return string 
     */
    public function getBlockReason()
    {
        return $this->blockReason;
    }

    /**
     * Set isMaster
     *
     * @param string $isMaster
     * @return Businesspartner
     */
    public function setIsMaster($isMaster)
    {
        $this->isMaster = $isMaster;

        return $this;
    }

    /**
     * Get isMaster
     *
     * @return string 
     */
    public function getIsMaster()
    {
        return $this->isMaster;
    }

    /**
     * Set webserviceLogin
     *
     * @param string $webserviceLogin
     * @return Businesspartner
     */
    public function setWebserviceLogin($webserviceLogin)
    {
        $this->webserviceLogin = $webserviceLogin;

        return $this;
    }

    /**
     * Get webserviceLogin
     *
     * @return string 
     */
    public function getWebserviceLogin()
    {
        return $this->webserviceLogin;
    }

    /**
     * Set clientId
     *
     * @param integer $clientId
     * @return Businesspartner
     */
    public function setClient($clientId)
    {
        $this->clientId = $clientId;

        return $this;
    }

    /**
     * Get clientId
     *
     * @return integer 
     */
    public function getClient()
    {
        return $this->clientId;
    }

    /**
     * Set paymentType
     *
     * @param string $paymentType
     * @return Businesspartner
     */
    public function setPaymentType($paymentType)
    {
        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * Get paymentType
     *
     * @return string 
     */
    public function getPaymentType()
    {
        return $this->paymentType;
    }

    /**
     * Set paymentDays
     *
     * @param integer $paymentDays
     * @return Businesspartner
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
     * Set description
     *
     * @param string $description
     * @return Businesspartner
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set creditAnalysis
     *
     * @param string $creditAnalysis
     * @return Businesspartner
     */
    public function setCreditAnalysis($creditAnalysis)
    {
        $this->creditAnalysis = $creditAnalysis;

        return $this;
    }

    /**
     * Get creditAnalysis
     *
     * @return string 
     */
    public function getCreditAnalysis()
    {
        return $this->creditAnalysis;
    }

    /**
     * Set registrationCodeCheck
     *
     * @param string $registrationCodeCheck
     * @return Businesspartner
     */
    public function setRegistrationCodeCheck($registrationCodeCheck)
    {
        $this->registrationCodeCheck = $registrationCodeCheck;

        return $this;
    }

    /**
     * Get registrationCodeCheck
     *
     * @return string 
     */
    public function getRegistrationCodeCheck()
    {
        return $this->registrationCodeCheck;
    }

    /**
     * Set adressCheck
     *
     * @param string $adressCheck
     * @return Businesspartner
     */
    public function setAdressCheck($adressCheck)
    {
        $this->adressCheck = $adressCheck;

        return $this;
    }

    /**
     * Get adressCheck
     *
     * @return string 
     */
    public function getAdressCheck()
    {
        return $this->adressCheck;
    }

    /**
     * Set creditDescription
     *
     * @param string $creditDescription
     * @return Businesspartner
     */
    public function setCreditDescription($creditDescription)
    {
        $this->creditDescription = $creditDescription;

        return $this;
    }

    /**
     * Get creditDescription
     *
     * @return string 
     */
    public function getCreditDescription()
    {
        return $this->creditDescription;
    }

    /**
     * Set companyName
     *
     * @param string $companyName
     * @return Businesspartner
     */
    public function setCompanyName($companyName)
    {
        $this->companyName = $companyName;

        return $this;
    }

    /**
     * Get companyName
     *
     * @return string 
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * Set partnerLimit
     *
     * @param float $partnerLimit
     * @return Businesspartner
     */
    public function setPartnerLimit($partnerLimit)
    {
        $this->partnerLimit = $partnerLimit;

        return $this;
    }

    /**
     * Get partnerLimit
     *
     * @return float 
     */
    public function getPartnerLimit()
    {
        return $this->partnerLimit;
    }

    /**
     * Set masterCode
     *
     * @param string $masterCode
     * @return Businesspartner
     */
    public function setMasterCode($masterCode)
    {
        $this->masterCode = $masterCode;

        return $this;
    }

    /**
     * Get masterCode
     *
     * @return string 
     */
    public function getMasterCode()
    {
        return $this->masterCode;
    }

    /**
     * Set workingDays
     *
     * @param string $workingDays
     * @return Businesspartner
     */
    public function setWorkingDays($workingDays)
    {
        $this->workingDays = $workingDays;

        return $this;
    }

    /**
     * Get workingDays
     *
     * @return string 
     */
    public function getWorkingDays()
    {
        return $this->workingDays;
    }

    /**
     * Set secondPaymentDays
     *
     * @param integer $secondPaymentDays
     * @return Businesspartner
     */
    public function setSecondPaymentDays($secondPaymentDays)
    {
        $this->secondPaymentDays = $secondPaymentDays;

        return $this;
    }

    /**
     * Get secondPaymentDays
     *
     * @return integer 
     */
    public function getSecondPaymentDays()
    {
        return $this->secondPaymentDays;
    }

    /**
     * Set secondWorkingDays
     *
     * @param string $secondWorkingDays
     * @return Businesspartner
     */
    public function setSecondWorkingDays($secondWorkingDays)
    {
        $this->secondWorkingDays = $secondWorkingDays;

        return $this;
    }

    /**
     * Get secondWorkingDays
     *
     * @return string 
     */
    public function getSecondWorkingDays()
    {
        return $this->secondWorkingDays;
    }

    /**
     * Set billingPeriod
     *
     * @param string $billingPeriod
     * @return Businesspartner
     */
    public function setBillingPeriod($billingPeriod)
    {
        $this->billingPeriod = $billingPeriod;

        return $this;
    }

    /**
     * Get billingPeriod
     *
     * @return string 
     */
    public function getBillingPeriod()
    {
        return $this->billingPeriod;
    }

    /**
     * Set typeSociety
     *
     * @param string $typeSociety
     * @return Businesspartner
     */
    public function setTypeSociety($typeSociety)
    {
        $this->typeSociety = $typeSociety;

        return $this;
    }

    /**
     * Get typeSociety
     *
     * @return string 
     */
    public function getTypeSociety()
    {
        return $this->typeSociety;
    }

    /**
     * Set mulct
     *
     * @param string $mulct
     * @return Businesspartner
     */
    public function setMulct($mulct)
    {
        $this->mulct = $mulct;

        return $this;
    }

    /**
     * Get mulct
     *
     * @return string 
     */
    public function getMulct()
    {
        return $this->mulct;
    }

    /**
     * Set registerDate
     *
     * @param \DateTime $registerDate
     * @return Businesspartner
     */
    public function setRegisterDate($registerDate)
    {
        $this->registerDate = $registerDate;

        return $this;
    }

    /**
     * Get registerDate
     *
     * @return \DateTime 
     */
    public function getRegisterDate()
    {
        return $this->registerDate;
    }

    /**
     * Set adressNumber
     *
     * @param string $adressNumber
     * @return Businesspartner
     */
    public function setAdressNumber($adressNumber)
    {
        $this->adressNumber = $adressNumber;

        return $this;
    }

    /**
     * Get adressNumber
     *
     * @return string 
     */
    public function getAdressNumber()
    {
        return $this->adressNumber;
    }

    /**
     * Set adressComplement
     *
     * @param string $adressComplement
     * @return Businesspartner
     */
    public function setAdressComplement($adressComplement)
    {
        $this->adressComplement = $adressComplement;

        return $this;
    }

    /**
     * Get adressComplement
     *
     * @return string 
     */
    public function getAdressComplement()
    {
        return $this->adressComplement;
    }

    /**
     * Set zipCode
     *
     * @param string $zipCode
     * @return Businesspartner
     */
    public function setZipCode($zipCode)
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    /**
     * Get zipCode
     *
     * @return string 
     */
    public function getZipCode()
    {
        return $this->zipCode;
    }

    /**
     * Set adressDistrict
     *
     * @param string $adressDistrict
     * @return Businesspartner
     */
    public function setAdressDistrict($adressDistrict)
    {
        $this->adressDistrict = $adressDistrict;

        return $this;
    }

    /**
     * Get adressDistrict
     *
     * @return string 
     */
    public function getAdressDistrict()
    {
        return $this->adressDistrict;
    }

    /**
     * Set commission
     *
     * @param string $commission
     * @return Businesspartner
     */
    public function setCommission($commission)
    {
        $this->commission = $commission;

        return $this;
    }

    /**
     * Get commission
     *
     * @return string 
     */
    public function getCommission()
    {
        return $this->commission;
    }

    /**
     * Set docs
     *
     * @param string $docs
     * @return Businesspartner
     */
    public function setDocs($docs)
    {
        $this->docs = $docs;

        return $this;
    }

    /**
     * Get docs
     *
     * @return string 
     */
    public function getDocs()
    {
        return $this->docs;
    }

    /**
     * Set phoneNumberAirline
     *
     * @param string $phoneNumberAirline
     * @return Businesspartner
     */
    public function setPhoneNumberAirline($phoneNumberAirline)
    {
        $this->phoneNumberAirline = $phoneNumberAirline;

        return $this;
    }

    /**
     * Get phoneNumberAirline
     *
     * @return string 
     */
    public function getPhoneNumberAirline()
    {
        return $this->phoneNumberAirline;
    }

    /**
     * Set celNumberAirline
     *
     * @param string $celNumberAirline
     * @return Businesspartner
     */
    public function setCelNumberAirline($celNumberAirline)
    {
        $this->celNumberAirline = $celNumberAirline;

        return $this;
    }

    /**
     * Get celNumberAirline
     *
     * @return string 
     */
    public function getCelNumberAirline()
    {
        return $this->celNumberAirline;
    }

    /**
     * Set financialContact
     *
     * @param string $financialContact
     * @return Businesspartner
     */
    public function setFinancialContact($financialContact)
    {
        $this->financialContact = $financialContact;

        return $this;
    }

    /**
     * Get financialContact
     *
     * @return string 
     */
    public function getFinancialContact()
    {
        return $this->financialContact;
    }

    /**
     * Set nameMother
     *
     * @param string $nameMother
     * @return Businesspartner
     */
    public function setNameMother($nameMother)
    {
        $this->nameMother = $nameMother;

        return $this;
    }

    /**
     * Get nameMother
     *
     * @return string 
     */
    public function getNameMother()
    {
        return $this->nameMother;
    }

    /**
     * Set limitMargin
     *
     * @param integer $limitMargin
     * @return Businesspartner
     */
    public function setLimitMargin($limitMargin)
    {
        $this->limitMargin = $limitMargin;

        return $this;
    }

    /**
     * Get limitMargin
     *
     * @return integer 
     */
    public function getLimitMargin()
    {
        return $this->limitMargin;
    }

    /**
     * Set finnancialEmail
     *
     * @param string $finnancialEmail
     * @return Businesspartner
     */
    public function setFinnancialEmail($finnancialEmail)
    {
        $this->finnancialEmail = $finnancialEmail;

        return $this;
    }

    /**
     * Get finnancialEmail
     *
     * @return string 
     */
    public function getFinnancialEmail()
    {
        return $this->finnancialEmail;
    }

    /**
     * Set interest
     *
     * @param string $interest
     * @return Businesspartner
     */
    public function setInterest($interest)
    {
        $this->interest = $interest;

        return $this;
    }

    /**
     * Get interest
     *
     * @return string 
     */
    public function getInterest()
    {
        return $this->interest;
    }

    /**
     * Set daysToBoarding
     *
     * @param integer $daysToBoarding
     * @return Businesspartner
     */
    public function setDaysToBoarding($daysToBoarding)
    {
        $this->daysToBoarding = $daysToBoarding;

        return $this;
    }

    /**
     * Get daysToBoarding
     *
     * @return integer 
     */
    public function getDaysToBoarding()
    {
        return $this->daysToBoarding;
    }

    /**
     * Set adressFinnancial
     *
     * @param string $adressFinnancial
     * @return Businesspartner
     */
    public function setAdressFinnancial($adressFinnancial)
    {
        $this->adressFinnancial = $adressFinnancial;

        return $this;
    }

    /**
     * Get adressFinnancial
     *
     * @return string 
     */
    public function getAdressFinnancial()
    {
        return $this->adressFinnancial;
    }

    /**
     * Set adressComplementFinnancial
     *
     * @param string $adressComplementFinnancial
     * @return Businesspartner
     */
    public function setAdressComplementFinnancial($adressComplementFinnancial)
    {
        $this->adressComplementFinnancial = $adressComplementFinnancial;

        return $this;
    }

    /**
     * Get adressComplementFinnancial
     *
     * @return string 
     */
    public function getAdressComplementFinnancial()
    {
        return $this->adressComplementFinnancial;
    }

    /**
     * Set adressDistrictFinnancial
     *
     * @param string $adressDistrictFinnancial
     * @return Businesspartner
     */
    public function setAdressDistrictFinnancial($adressDistrictFinnancial)
    {
        $this->adressDistrictFinnancial = $adressDistrictFinnancial;

        return $this;
    }

    /**
     * Get adressDistrictFinnancial
     *
     * @return string 
     */
    public function getAdressDistrictFinnancial()
    {
        return $this->adressDistrictFinnancial;
    }

    /**
     * Set adressNumberFinnancial
     *
     * @param string $adressNumberFinnancial
     * @return Businesspartner
     */
    public function setAdressNumberFinnancial($adressNumberFinnancial)
    {
        $this->adressNumberFinnancial = $adressNumberFinnancial;

        return $this;
    }

    /**
     * Get adressNumberFinnancial
     *
     * @return string 
     */
    public function getAdressNumberFinnancial()
    {
        return $this->adressNumberFinnancial;
    }

    /**
     * Set zipCodeFinnancial
     *
     * @param string $zipCodeFinnancial
     * @return Businesspartner
     */
    public function setZipCodeFinnancial($zipCodeFinnancial)
    {
        $this->zipCodeFinnancial = $zipCodeFinnancial;

        return $this;
    }

    /**
     * Get zipCodeFinnancial
     *
     * @return string 
     */
    public function getZipCodeFinnancial()
    {
        return $this->zipCodeFinnancial;
    }

    /**
     * Set contact
     *
     * @param string $contact
     * @return Businesspartner
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }

    /**
     * Get contact
     *
     * @return string 
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Set useCommission
     *
     * @param string $useCommission
     * @return Businesspartner
     */
    public function setUseCommission($useCommission)
    {
        $this->useCommission = $useCommission;

        return $this;
    }

    /**
     * Get useCommission
     *
     * @return string 
     */
    public function getUseCommission()
    {
        return $this->useCommission;
    }

    /**
     * Set subClient
     *
     * @param string $subClient
     * @return Businesspartner
     */
    public function setSubClient($subClient)
    {
        $this->subClient = $subClient;

        return $this;
    }

    /**
     * Get subClient
     *
     * @return string 
     */
    public function getSubClient()
    {
        return $this->subClient;
    }

    /**
     * Set lastPasswordDate
     *
     * @param \DateTime $lastPasswordDate
     * @return Businesspartner
     */
    public function setLastPasswordDate($lastPasswordDate)
    {
        $this->lastPasswordDate = $lastPasswordDate;

        return $this;
    }

    /**
     * Get lastPasswordDate
     *
     * @return \DateTime 
     */
    public function getLastPasswordDate()
    {
        return $this->lastPasswordDate;
    }

    /**
     * Set avoidDailyReminder
     *
     * @param string $avoidDailyReminder
     * @return Businesspartner
     */
    public function setAvoidDailyReminder($avoidDailyReminder)
    {
        $this->avoidDailyReminder = $avoidDailyReminder;

        return $this;
    }

    /**
     * Get avoidDailyReminder
     *
     * @return string 
     */
    public function getAvoidDailyReminder()
    {
        return $this->avoidDailyReminder;
    }

    /**
     * Set avoidSecondWayBillets
     *
     * @param string $avoidSecondWayBillets
     * @return Businesspartner
     */
    public function setAvoidSecondWayBillets($avoidSecondWayBillets)
    {
        $this->avoidSecondWayBillets = $avoidSecondWayBillets;

        return $this;
    }

    /**
     * Get avoidSecondWayBillets
     *
     * @return string 
     */
    public function getAvoidSecondWayBillets()
    {
        return $this->avoidSecondWayBillets;
    }

    /**
     * Set origin
     *
     * @param string $origin
     * @return Businesspartner
     */
    public function setOrigin($origin)
    {
        $this->origin = $origin;

        return $this;
    }

    /**
     * Get origin
     *
     * @return string 
     */
    public function getOrigin()
    {
        return $this->origin;
    }

    /**
     * Set bankSlipSocialName
     *
     * @param string $bankSlipSocialName
     * @return Businesspartner
     */
    public function setBankSlipSocialName($bankSlipSocialName)
    {
        $this->bankSlipSocialName = $bankSlipSocialName;

        return $this;
    }

    /**
     * Get bankSlipSocialName
     *
     * @return string 
     */
    public function getBankSlipSocialName()
    {
        return $this->bankSlipSocialName;
    }

    /**
     * Set plan
     *
     * @param \SalePlans $plan
     * @return Businesspartner
     */
    public function setPlan(\SalePlans $plan = null)
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Get plan
     *
     * @return \SalePlans 
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * Set city
     *
     * @param \City $city
     * @return Businesspartner
     */
    public function setCity(\City $city = null)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get city
     *
     * @return \City 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set operationPlan
     *
     * @param \AirlineOperationsPlan $operationPlan
     * @return Businesspartner
     */
    public function setOperationPlan(\AirlineOperationsPlan $operationPlan = null)
    {
        $this->operationPlan = $operationPlan;

        return $this;
    }

    /**
     * Get operationPlan
     *
     * @return \AirlineOperationsPlan 
     */
    public function getOperationPlan()
    {
        return $this->operationPlan;
    }

    /**
     * Set cityFinnancial
     *
     * @param \City $cityFinnancial
     * @return Businesspartner
     */
    public function setCityFinnancial(\City $cityFinnancial = null)
    {
        $this->cityFinnancial = $cityFinnancial;

        return $this;
    }

    /**
     * Get cityFinnancial
     *
     * @return \City 
     */
    public function getCityFinnancial()
    {
        return $this->cityFinnancial;
    }

    /**
     * Set dealer
     *
     * @param \Businesspartner $dealer
     * @return Businesspartner
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
     * Set masterClient
     *
     * @param \Businesspartner $masterClient
     * @return Businesspartner
     */
    public function setMasterClient(\Businesspartner $masterClient = null)
    {
        $this->masterClient = $masterClient;

        return $this;
    }

    /**
     * Get masterClient
     *
     * @return \Businesspartner 
     */
    public function getMasterClient()
    {
        return $this->masterClient;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="conta_azul_token", type="string", length=255, nullable=true)
     */
    private $contaAzulToken;

    /**
     * @var string
     *
     * @ORM\Column(name="conta_azul_refresh_token", type="string", length=255, nullable=true)
     */
    private $contaAzulRefreshToken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="conta_azul_last_update", type="datetime", nullable=true)
     */
    private $contaAzulLastUpdate;


    /**
     * Set contaAzulToken
     *
     * @param string $contaAzulToken
     * @return Businesspartner
     */
    public function setContaAzulToken($contaAzulToken)
    {
        $this->contaAzulToken = $contaAzulToken;

        return $this;
    }

    /**
     * Get contaAzulToken
     *
     * @return string 
     */
    public function getContaAzulToken()
    {
        return $this->contaAzulToken;
    }

    /**
     * Set contaAzulRefreshToken
     *
     * @param string $contaAzulRefreshToken
     * @return Businesspartner
     */
    public function setContaAzulRefreshToken($contaAzulRefreshToken)
    {
        $this->contaAzulRefreshToken = $contaAzulRefreshToken;

        return $this;
    }

    /**
     * Get contaAzulRefreshToken
     *
     * @return string 
     */
    public function getContaAzulRefreshToken()
    {
        return $this->contaAzulRefreshToken;
    }

    /**
     * Set contaAzulLastUpdate
     *
     * @param \DateTime $contaAzulLastUpdate
     * @return Businesspartner
     */
    public function setContaAzulLastUpdate($contaAzulLastUpdate)
    {
        $this->contaAzulLastUpdate = $contaAzulLastUpdate;

        return $this;
    }

    /**
     * Get contaAzulLastUpdate
     *
     * @return \DateTime 
     */
    public function getContaAzulLastUpdate()
    {
        return $this->contaAzulLastUpdate;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="conta_azul_id", type="string", length=255, nullable=true)
     */
    private $contaAzulId;


    /**
     * Set contaAzulId
     *
     * @param string $contaAzulId
     * @return Businesspartner
     */
    public function setContaAzulId($contaAzulId)
    {
        $this->contaAzulId = $contaAzulId;

        return $this;
    }

    /**
     * Get contaAzulId
     *
     * @return string 
     */
    public function getContaAzulId()
    {
        return $this->contaAzulId;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="system_name", type="string", length=240, nullable=true)
     */
    private $systemName;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_url", type="string", length=240, nullable=true)
     */
    private $logoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="label_name", type="string", length=240, nullable=true)
     */
    private $labelName;

    /**
     * @var string
     *
     * @ORM\Column(name="label_description", type="string", length=240, nullable=true)
     */
    private $labelDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="label_adress", type="string", length=240, nullable=true)
     */
    private $labelAdress;

    /**
     * @var string
     *
     * @ORM\Column(name="label_phone", type="string", length=240, nullable=true)
     */
    private $labelPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="label_email", type="string", length=240, nullable=true)
     */
    private $labelEmail;

    /**
     * @var string
     *
     * @ORM\Column(name="logo_url_small", type="string", length=240, nullable=true)
     */
    private $logoUrlSmall;


    /**
     * Set systemName
     *
     * @param string $systemName
     * @return Businesspartner
     */
    public function setSystemName($systemName)
    {
        $this->systemName = $systemName;

        return $this;
    }

    /**
     * Get systemName
     *
     * @return string 
     */
    public function getSystemName()
    {
        return $this->systemName;
    }

    /**
     * Set logoUrl
     *
     * @param string $logoUrl
     * @return Businesspartner
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logoUrl = $logoUrl;

        return $this;
    }

    /**
     * Get logoUrl
     *
     * @return string 
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
    }

    /**
     * Set labelName
     *
     * @param string $labelName
     * @return Businesspartner
     */
    public function setLabelName($labelName)
    {
        $this->labelName = $labelName;

        return $this;
    }

    /**
     * Get labelName
     *
     * @return string 
     */
    public function getLabelName()
    {
        return $this->labelName;
    }

    /**
     * Set labelDescription
     *
     * @param string $labelDescription
     * @return Businesspartner
     */
    public function setLabelDescription($labelDescription)
    {
        $this->labelDescription = $labelDescription;

        return $this;
    }

    /**
     * Get labelDescription
     *
     * @return string 
     */
    public function getLabelDescription()
    {
        return $this->labelDescription;
    }

    /**
     * Set labelAdress
     *
     * @param string $labelAdress
     * @return Businesspartner
     */
    public function setLabelAdress($labelAdress)
    {
        $this->labelAdress = $labelAdress;

        return $this;
    }

    /**
     * Get labelAdress
     *
     * @return string 
     */
    public function getLabelAdress()
    {
        return $this->labelAdress;
    }

    /**
     * Set labelPhone
     *
     * @param string $labelPhone
     * @return Businesspartner
     */
    public function setLabelPhone($labelPhone)
    {
        $this->labelPhone = $labelPhone;

        return $this;
    }

    /**
     * Get labelPhone
     *
     * @return string 
     */
    public function getLabelPhone()
    {
        return $this->labelPhone;
    }

    /**
     * Set labelEmail
     *
     * @param string $labelEmail
     * @return Businesspartner
     */
    public function setLabelEmail($labelEmail)
    {
        $this->labelEmail = $labelEmail;

        return $this;
    }

    /**
     * Get labelEmail
     *
     * @return string 
     */
    public function getLabelEmail()
    {
        return $this->labelEmail;
    }

    /**
     * Set logoUrlSmall
     *
     * @param string $logoUrlSmall
     * @return Businesspartner
     */
    public function setLogoUrlSmall($logoUrlSmall)
    {
        $this->logoUrlSmall = $logoUrlSmall;

        return $this;
    }

    /**
     * Get logoUrlSmall
     *
     * @return string 
     */
    public function getLogoUrlSmall()
    {
        return $this->logoUrlSmall;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="suggestion_new_data", type="string", length=1024, nullable=true)
     */
    private $suggestionNewData;


    /**
     * Set suggestionNewData
     *
     * @param string $suggestionNewData
     * @return Businesspartner
     */
    public function setSuggestionNewData($suggestionNewData)
    {
        $this->suggestionNewData = $suggestionNewData;

        return $this;
    }

    /**
     * Get suggestionNewData
     *
     * @return string 
     */
    public function getSuggestionNewData()
    {
        return $this->suggestionNewData;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="client_markup_type", type="string", nullable=true)
     */
    private $clientMarkupType = 'P';

    /**
     * @var float
     *
     * @ORM\Column(name="client_markup", type="float", precision=20, scale=2, nullable=true)
     */
    private $clientMarkup = '0.00';


    /**
     * Set clientMarkupType
     *
     * @param string $clientMarkupType
     * @return Businesspartner
     */
    public function setClientMarkupType($clientMarkupType)
    {
        $this->clientMarkupType = $clientMarkupType;

        return $this;
    }

    /**
     * Get clientMarkupType
     *
     * @return string 
     */
    public function getClientMarkupType()
    {
        return $this->clientMarkupType;
    }

    /**
     * Set clientMarkup
     *
     * @param float $clientMarkup
     * @return Businesspartner
     */
    public function setClientMarkup($clientMarkup)
    {
        $this->clientMarkup = $clientMarkup;

        return $this;
    }

    /**
     * Get clientMarkup
     *
     * @return float 
     */
    public function getClientMarkup()
    {
        return $this->clientMarkup;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="cpf_bank_account", type="string", length=255, nullable=true)
     */
    private $cpfBankAccount;

    /**
     * @var string
     *
     * @ORM\Column(name="name_bank_account", type="string", length=255, nullable=true)
     */
    private $nameBankAccount;


    /**
     * Set cpfBankAccount
     *
     * @param string $cpfBankAccount
     * @return Businesspartner
     */
    public function setCpfBankAccount($cpfBankAccount)
    {
        $this->cpfBankAccount = $cpfBankAccount;

        return $this;
    }

    /**
     * Get cpfBankAccount
     *
     * @return string 
     */
    public function getCpfBankAccount()
    {
        return $this->cpfBankAccount;
    }

    /**
     * Set nameBankAccount
     *
     * @param string $nameBankAccount
     * @return Businesspartner
     */
    public function setNameBankAccount($nameBankAccount)
    {
        $this->nameBankAccount = $nameBankAccount;

        return $this;
    }

    /**
     * Get nameBankAccount
     *
     * @return string 
     */
    public function getNameBankAccount()
    {
        return $this->nameBankAccount;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="prefixo", type="string", length=16, nullable=true)
     */
    private $prefixo;


    /**
     * Set prefixo
     *
     * @param string $prefixo
     * @return Businesspartner
     */
    public function setPrefixo($prefixo)
    {
        $this->prefixo = $prefixo;

        return $this;
    }

    /**
     * Get prefixo
     *
     * @return string 
     */
    public function getPrefixo()
    {
        return $this->prefixo;
    }
    /**
     * @var boolean
     *
     * @ORM\Column(name="whitelabel", type="boolean", nullable=true)
     */
    private $whitelabel = '0';


    /**
     * Set whitelabel
     *
     * @param boolean $whitelabel
     * @return Businesspartner
     */
    public function setWhitelabel($whitelabel)
    {
        $this->whitelabel = $whitelabel;

        return $this;
    }

    /**
     * Get whitelabel
     *
     * @return boolean 
     */
    public function getWhitelabel()
    {
        return $this->whitelabel;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="url_whitelabel", type="string", length=255, nullable=true)
     */
    private $urlWhitelabel;


    /**
     * Set urlWhitelabel
     *
     * @param string $urlWhitelabel
     * @return Businesspartner
     */
    public function setUrlWhitelabel($urlWhitelabel)
    {
        $this->urlWhitelabel = $urlWhitelabel;

        return $this;
    }

    /**
     * Get urlWhitelabel
     *
     * @return string 
     */
    public function getUrlWhitelabel()
    {
        return $this->urlWhitelabel;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="split_payment_data", type="text", nullable=true)
     */
    private $splitPaymentData;


    /**
     * Set splitPaymentData
     *
     * @param string $splitPaymentData
     * @return Businesspartner
     */
    public function setSplitPaymentData($splitPaymentData)
    {
        $this->splitPaymentData = $splitPaymentData;

        return $this;
    }

    /**
     * Get splitPaymentData
     *
     * @return string 
     */
    public function getSplitPaymentData()
    {
        return $this->splitPaymentData;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="chip_number", type="string", length=160, nullable=true)
     */
    private $chipNumber;


    /**
     * Set chipNumber
     *
     * @param string $chipNumber
     * @return Businesspartner
     */
    public function setChipNumber($chipNumber)
    {
        $this->chipNumber = $chipNumber;

        return $this;
    }

    /**
     * Get chipNumber
     *
     * @return string 
     */
    public function getChipNumber()
    {
        return $this->chipNumber;
    }
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_credit_analysis", type="datetime", nullable=true)
     */
    private $lastCreditAnalysis;


    /**
     * Set lastCreditAnalysis
     *
     * @param \DateTime $lastCreditAnalysis
     * @return Businesspartner
     */
    public function setLastCreditAnalysis($lastCreditAnalysis)
    {
        $this->lastCreditAnalysis = $lastCreditAnalysis;

        return $this;
    }

    /**
     * Get lastCreditAnalysis
     *
     * @return \DateTime 
     */
    public function getLastCreditAnalysis()
    {
        return $this->lastCreditAnalysis;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="bank_operation", type="string", length=255, nullable=true)
     */
    private $bankOperation;

    /**
     * @var string
     *
     * @ORM\Column(name="bank_name_owner", type="string", length=255, nullable=true)
     */
    private $bankNameOwner;


    /**
     * Set bankOperation
     *
     * @param string $bankOperation
     * @return Businesspartner
     */
    public function setBankOperation($bankOperation)
    {
        $this->bankOperation = $bankOperation;

        return $this;
    }

    /**
     * Get bankOperation
     *
     * @return string 
     */
    public function getBankOperation()
    {
        return $this->bankOperation;
    }

    /**
     * Set bankNameOwner
     *
     * @param string $bankNameOwner
     * @return Businesspartner
     */
    public function setBankNameOwner($bankNameOwner)
    {
        $this->bankNameOwner = $bankNameOwner;

        return $this;
    }

    /**
     * Get bankNameOwner
     *
     * @return string 
     */
    public function getBankNameOwner()
    {
        return $this->bankNameOwner;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="cpf_name_owner", type="string", length=255, nullable=true)
     */
    private $cpfNameOwner;


    /**
     * Set cpfNameOwner
     *
     * @param string $cpfNameOwner
     * @return Businesspartner
     */
    public function setCpfNameOwner($cpfNameOwner)
    {
        $this->cpfNameOwner = $cpfNameOwner;

        return $this;
    }

    /**
     * Get cpfNameOwner
     *
     * @return string 
     */
    public function getCpfNameOwner()
    {
        return $this->cpfNameOwner;
    }
}
