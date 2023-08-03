<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * UserPermission
 *
 * @ORM\Table(name="user_permission", indexes={@ORM\Index(name="user_id", columns={"user_id"})})
 * @ORM\Entity
 */
class UserPermission
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
     * @ORM\Column(name="purchase", type="string", length=5, nullable=false)
     */
    private $purchase = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="wizard_purchase", type="string", length=5, nullable=false)
     */
    private $wizardPurchase = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="sale", type="string", length=5, nullable=false)
     */
    private $sale = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="wizard_sale", type="string", length=5, nullable=false)
     */
    private $wizardSale = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="miles_bench", type="string", length=5, nullable=false)
     */
    private $milesBench = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="financial", type="string", length=5, nullable=false)
     */
    private $financial = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="credit_card", type="string", length=5, nullable=false)
     */
    private $creditCard = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="users", type="string", length=5, nullable=false)
     */
    private $users = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="change_sale", type="string", length=5, nullable=false)
     */
    private $changeSale = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="change_miles", type="string", length=5, nullable=false)
     */
    private $changeMiles = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="commercial", type="string", length=5, nullable=false)
     */
    private $commercial = 'false';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sunday_in", type="datetime", nullable=true)
     */
    private $sundayIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="monday_in", type="datetime", nullable=true)
     */
    private $mondayIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tuesday_in", type="datetime", nullable=true)
     */
    private $tuesdayIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="wednesday_in", type="datetime", nullable=true)
     */
    private $wednesdayIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="thursday_in", type="datetime", nullable=true)
     */
    private $thursdayIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="friday_in", type="datetime", nullable=true)
     */
    private $fridayIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="saturday_in", type="datetime", nullable=true)
     */
    private $saturdayIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sunday_out", type="datetime", nullable=true)
     */
    private $sundayOut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="monday_out", type="datetime", nullable=true)
     */
    private $mondayOut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="tuesday_out", type="datetime", nullable=true)
     */
    private $tuesdayOut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="wednesday_out", type="datetime", nullable=true)
     */
    private $wednesdayOut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="thursday_out", type="datetime", nullable=true)
     */
    private $thursdayOut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="friday_out", type="datetime", nullable=true)
     */
    private $fridayOut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="saturday_out", type="datetime", nullable=true)
     */
    private $saturdayOut;

    /**
     * @var string
     *
     * @ORM\Column(name="permission", type="string", length=5, nullable=false)
     */
    private $permission = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="dealer", type="string", length=1500, nullable=true)
     */
    private $dealer;

    /**
     * @var string
     *
     * @ORM\Column(name="pagseguro", type="string", length=5, nullable=false)
     */
    private $pagseguro = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="intern_refund", type="string", length=5, nullable=false)
     */
    private $internRefund = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="intern_commercial", type="string", length=5, nullable=false)
     */
    private $internCommercial = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="human_resources", type="string", length=5, nullable=false)
     */
    private $humanResources = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="sale_plans_edit", type="string", length=5, nullable=true)
     */
    private $salePlansEdit = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="conference", type="string", length=5, nullable=true)
     */
    private $conference = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="online_online_order", type="string", length=5, nullable=false)
     */
    private $onlineOnlineOrder = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="online_balance_order", type="string", length=5, nullable=false)
     */
    private $onlineBalanceOrder = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="online_cards_in_use", type="string", length=5, nullable=false)
     */
    private $onlineCardsInUse = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="purchase_provider", type="string", length=5, nullable=false)
     */
    private $purchaseProvider = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="purchase_payment_pruchase", type="string", length=5, nullable=false)
     */
    private $purchasePaymentPruchase = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="purchase_end_pruchase", type="string", length=5, nullable=false)
     */
    private $purchaseEndPruchase = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="purchase_pruchases", type="string", length=5, nullable=false)
     */
    private $purchasePruchases = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="purchase_cards_pendency", type="string", length=5, nullable=false)
     */
    private $purchaseCardsPendency = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="sale_clients", type="string", length=5, nullable=false)
     */
    private $saleClients = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="sale_balance_clients", type="string", length=5, nullable=false)
     */
    private $saleBalanceClients = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="sale_future_boardings", type="string", length=5, nullable=false)
     */
    private $saleFutureBoardings = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="sale_refund_cancel", type="string", length=5, nullable=false)
     */
    private $saleRefundCancel = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="sale_revert_refund", type="string", length=5, nullable=true)
     */
    private $saleRevertRefund = 'false';

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;


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
     * Set purchase
     *
     * @param string $purchase
     * @return UserPermission
     */
    public function setPurchase($purchase)
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * Get purchase
     *
     * @return string 
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * Set wizardPurchase
     *
     * @param string $wizardPurchase
     * @return UserPermission
     */
    public function setWizardPurchase($wizardPurchase)
    {
        $this->wizardPurchase = $wizardPurchase;

        return $this;
    }

    /**
     * Get wizardPurchase
     *
     * @return string 
     */
    public function getWizardPurchase()
    {
        return $this->wizardPurchase;
    }

    /**
     * Set sale
     *
     * @param string $sale
     * @return UserPermission
     */
    public function setSale($sale)
    {
        $this->sale = $sale;

        return $this;
    }

    /**
     * Get sale
     *
     * @return string 
     */
    public function getSale()
    {
        return $this->sale;
    }

    /**
     * Set wizardSale
     *
     * @param string $wizardSale
     * @return UserPermission
     */
    public function setWizardSale($wizardSale)
    {
        $this->wizardSale = $wizardSale;

        return $this;
    }

    /**
     * Get wizardSale
     *
     * @return string 
     */
    public function getWizardSale()
    {
        return $this->wizardSale;
    }

    /**
     * Set milesBench
     *
     * @param string $milesBench
     * @return UserPermission
     */
    public function setMilesBench($milesBench)
    {
        $this->milesBench = $milesBench;

        return $this;
    }

    /**
     * Get milesBench
     *
     * @return string 
     */
    public function getMilesBench()
    {
        return $this->milesBench;
    }

    /**
     * Set financial
     *
     * @param string $financial
     * @return UserPermission
     */
    public function setFinancial($financial)
    {
        $this->financial = $financial;

        return $this;
    }

    /**
     * Get financial
     *
     * @return string 
     */
    public function getFinancial()
    {
        return $this->financial;
    }

    /**
     * Set creditCard
     *
     * @param string $creditCard
     * @return UserPermission
     */
    public function setCreditCard($creditCard)
    {
        $this->creditCard = $creditCard;

        return $this;
    }

    /**
     * Get creditCard
     *
     * @return string 
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * Set users
     *
     * @param string $users
     * @return UserPermission
     */
    public function setUsers($users)
    {
        $this->users = $users;

        return $this;
    }

    /**
     * Get users
     *
     * @return string 
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set changeSale
     *
     * @param string $changeSale
     * @return UserPermission
     */
    public function setChangeSale($changeSale)
    {
        $this->changeSale = $changeSale;

        return $this;
    }

    /**
     * Get changeSale
     *
     * @return string 
     */
    public function getChangeSale()
    {
        return $this->changeSale;
    }

    /**
     * Set changeMiles
     *
     * @param string $changeMiles
     * @return UserPermission
     */
    public function setChangeMiles($changeMiles)
    {
        $this->changeMiles = $changeMiles;

        return $this;
    }

    /**
     * Get changeMiles
     *
     * @return string 
     */
    public function getChangeMiles()
    {
        return $this->changeMiles;
    }

    /**
     * Set commercial
     *
     * @param string $commercial
     * @return UserPermission
     */
    public function setCommercial($commercial)
    {
        $this->commercial = $commercial;

        return $this;
    }

    /**
     * Get commercial
     *
     * @return string 
     */
    public function getCommercial()
    {
        return $this->commercial;
    }

    /**
     * Set sundayIn
     *
     * @param \DateTime $sundayIn
     * @return UserPermission
     */
    public function setSundayIn($sundayIn)
    {
        $this->sundayIn = $sundayIn;

        return $this;
    }

    /**
     * Get sundayIn
     *
     * @return \DateTime 
     */
    public function getSundayIn()
    {
        return $this->sundayIn;
    }

    /**
     * Set mondayIn
     *
     * @param \DateTime $mondayIn
     * @return UserPermission
     */
    public function setMondayIn($mondayIn)
    {
        $this->mondayIn = $mondayIn;

        return $this;
    }

    /**
     * Get mondayIn
     *
     * @return \DateTime 
     */
    public function getMondayIn()
    {
        return $this->mondayIn;
    }

    /**
     * Set tuesdayIn
     *
     * @param \DateTime $tuesdayIn
     * @return UserPermission
     */
    public function setTuesdayIn($tuesdayIn)
    {
        $this->tuesdayIn = $tuesdayIn;

        return $this;
    }

    /**
     * Get tuesdayIn
     *
     * @return \DateTime 
     */
    public function getTuesdayIn()
    {
        return $this->tuesdayIn;
    }

    /**
     * Set wednesdayIn
     *
     * @param \DateTime $wednesdayIn
     * @return UserPermission
     */
    public function setWednesdayIn($wednesdayIn)
    {
        $this->wednesdayIn = $wednesdayIn;

        return $this;
    }

    /**
     * Get wednesdayIn
     *
     * @return \DateTime 
     */
    public function getWednesdayIn()
    {
        return $this->wednesdayIn;
    }

    /**
     * Set thursdayIn
     *
     * @param \DateTime $thursdayIn
     * @return UserPermission
     */
    public function setThursdayIn($thursdayIn)
    {
        $this->thursdayIn = $thursdayIn;

        return $this;
    }

    /**
     * Get thursdayIn
     *
     * @return \DateTime 
     */
    public function getThursdayIn()
    {
        return $this->thursdayIn;
    }

    /**
     * Set fridayIn
     *
     * @param \DateTime $fridayIn
     * @return UserPermission
     */
    public function setFridayIn($fridayIn)
    {
        $this->fridayIn = $fridayIn;

        return $this;
    }

    /**
     * Get fridayIn
     *
     * @return \DateTime 
     */
    public function getFridayIn()
    {
        return $this->fridayIn;
    }

    /**
     * Set saturdayIn
     *
     * @param \DateTime $saturdayIn
     * @return UserPermission
     */
    public function setSaturdayIn($saturdayIn)
    {
        $this->saturdayIn = $saturdayIn;

        return $this;
    }

    /**
     * Get saturdayIn
     *
     * @return \DateTime 
     */
    public function getSaturdayIn()
    {
        return $this->saturdayIn;
    }

    /**
     * Set sundayOut
     *
     * @param \DateTime $sundayOut
     * @return UserPermission
     */
    public function setSundayOut($sundayOut)
    {
        $this->sundayOut = $sundayOut;

        return $this;
    }

    /**
     * Get sundayOut
     *
     * @return \DateTime 
     */
    public function getSundayOut()
    {
        return $this->sundayOut;
    }

    /**
     * Set mondayOut
     *
     * @param \DateTime $mondayOut
     * @return UserPermission
     */
    public function setMondayOut($mondayOut)
    {
        $this->mondayOut = $mondayOut;

        return $this;
    }

    /**
     * Get mondayOut
     *
     * @return \DateTime 
     */
    public function getMondayOut()
    {
        return $this->mondayOut;
    }

    /**
     * Set tuesdayOut
     *
     * @param \DateTime $tuesdayOut
     * @return UserPermission
     */
    public function setTuesdayOut($tuesdayOut)
    {
        $this->tuesdayOut = $tuesdayOut;

        return $this;
    }

    /**
     * Get tuesdayOut
     *
     * @return \DateTime 
     */
    public function getTuesdayOut()
    {
        return $this->tuesdayOut;
    }

    /**
     * Set wednesdayOut
     *
     * @param \DateTime $wednesdayOut
     * @return UserPermission
     */
    public function setWednesdayOut($wednesdayOut)
    {
        $this->wednesdayOut = $wednesdayOut;

        return $this;
    }

    /**
     * Get wednesdayOut
     *
     * @return \DateTime 
     */
    public function getWednesdayOut()
    {
        return $this->wednesdayOut;
    }

    /**
     * Set thursdayOut
     *
     * @param \DateTime $thursdayOut
     * @return UserPermission
     */
    public function setThursdayOut($thursdayOut)
    {
        $this->thursdayOut = $thursdayOut;

        return $this;
    }

    /**
     * Get thursdayOut
     *
     * @return \DateTime 
     */
    public function getThursdayOut()
    {
        return $this->thursdayOut;
    }

    /**
     * Set fridayOut
     *
     * @param \DateTime $fridayOut
     * @return UserPermission
     */
    public function setFridayOut($fridayOut)
    {
        $this->fridayOut = $fridayOut;

        return $this;
    }

    /**
     * Get fridayOut
     *
     * @return \DateTime 
     */
    public function getFridayOut()
    {
        return $this->fridayOut;
    }

    /**
     * Set saturdayOut
     *
     * @param \DateTime $saturdayOut
     * @return UserPermission
     */
    public function setSaturdayOut($saturdayOut)
    {
        $this->saturdayOut = $saturdayOut;

        return $this;
    }

    /**
     * Get saturdayOut
     *
     * @return \DateTime 
     */
    public function getSaturdayOut()
    {
        return $this->saturdayOut;
    }

    /**
     * Set permission
     *
     * @param string $permission
     * @return UserPermission
     */
    public function setPermission($permission)
    {
        $this->permission = $permission;

        return $this;
    }

    /**
     * Get permission
     *
     * @return string 
     */
    public function getPermission()
    {
        return $this->permission;
    }

    /**
     * Set dealer
     *
     * @param string $dealer
     * @return UserPermission
     */
    public function setDealer($dealer)
    {
        $this->dealer = $dealer;

        return $this;
    }

    /**
     * Get dealer
     *
     * @return string 
     */
    public function getDealer()
    {
        return $this->dealer;
    }

    /**
     * Set pagseguro
     *
     * @param string $pagseguro
     * @return UserPermission
     */
    public function setPagseguro($pagseguro)
    {
        $this->pagseguro = $pagseguro;

        return $this;
    }

    /**
     * Get pagseguro
     *
     * @return string 
     */
    public function getPagseguro()
    {
        return $this->pagseguro;
    }

    /**
     * Set internRefund
     *
     * @param string $internRefund
     * @return UserPermission
     */
    public function setInternRefund($internRefund)
    {
        $this->internRefund = $internRefund;

        return $this;
    }

    /**
     * Get internRefund
     *
     * @return string 
     */
    public function getInternRefund()
    {
        return $this->internRefund;
    }

    /**
     * Set internCommercial
     *
     * @param string $internCommercial
     * @return UserPermission
     */
    public function setInternCommercial($internCommercial)
    {
        $this->internCommercial = $internCommercial;

        return $this;
    }

    /**
     * Get internCommercial
     *
     * @return string 
     */
    public function getInternCommercial()
    {
        return $this->internCommercial;
    }

    /**
     * Set humanResources
     *
     * @param string $humanResources
     * @return UserPermission
     */
    public function setHumanResources($humanResources)
    {
        $this->humanResources = $humanResources;

        return $this;
    }

    /**
     * Get humanResources
     *
     * @return string 
     */
    public function getHumanResources()
    {
        return $this->humanResources;
    }

    /**
     * Set salePlansEdit
     *
     * @param string $salePlansEdit
     * @return UserPermission
     */
    public function setSalePlansEdit($salePlansEdit)
    {
        $this->salePlansEdit = $salePlansEdit;

        return $this;
    }

    /**
     * Get salePlansEdit
     *
     * @return string 
     */
    public function getSalePlansEdit()
    {
        return $this->salePlansEdit;
    }

    /**
     * Set conference
     *
     * @param string $conference
     * @return UserPermission
     */
    public function setConference($conference)
    {
        $this->conference = $conference;

        return $this;
    }

    /**
     * Get conference
     *
     * @return string 
     */
    public function getConference()
    {
        return $this->conference;
    }

    /**
     * Set onlineOnlineOrder
     *
     * @param string $onlineOnlineOrder
     * @return UserPermission
     */
    public function setOnlineOnlineOrder($onlineOnlineOrder)
    {
        $this->onlineOnlineOrder = $onlineOnlineOrder;

        return $this;
    }

    /**
     * Get onlineOnlineOrder
     *
     * @return string 
     */
    public function getOnlineOnlineOrder()
    {
        return $this->onlineOnlineOrder;
    }

    /**
     * Set onlineBalanceOrder
     *
     * @param string $onlineBalanceOrder
     * @return UserPermission
     */
    public function setOnlineBalanceOrder($onlineBalanceOrder)
    {
        $this->onlineBalanceOrder = $onlineBalanceOrder;

        return $this;
    }

    /**
     * Get onlineBalanceOrder
     *
     * @return string 
     */
    public function getOnlineBalanceOrder()
    {
        return $this->onlineBalanceOrder;
    }

    /**
     * Set onlineCardsInUse
     *
     * @param string $onlineCardsInUse
     * @return UserPermission
     */
    public function setOnlineCardsInUse($onlineCardsInUse)
    {
        $this->onlineCardsInUse = $onlineCardsInUse;

        return $this;
    }

    /**
     * Get onlineCardsInUse
     *
     * @return string 
     */
    public function getOnlineCardsInUse()
    {
        return $this->onlineCardsInUse;
    }

    /**
     * Set purchaseProvider
     *
     * @param string $purchaseProvider
     * @return UserPermission
     */
    public function setPurchaseProvider($purchaseProvider)
    {
        $this->purchaseProvider = $purchaseProvider;

        return $this;
    }

    /**
     * Get purchaseProvider
     *
     * @return string 
     */
    public function getPurchaseProvider()
    {
        return $this->purchaseProvider;
    }

    /**
     * Set purchasePaymentPruchase
     *
     * @param string $purchasePaymentPruchase
     * @return UserPermission
     */
    public function setPurchasePaymentPruchase($purchasePaymentPruchase)
    {
        $this->purchasePaymentPruchase = $purchasePaymentPruchase;

        return $this;
    }

    /**
     * Get purchasePaymentPruchase
     *
     * @return string 
     */
    public function getPurchasePaymentPruchase()
    {
        return $this->purchasePaymentPruchase;
    }

    /**
     * Set purchaseEndPruchase
     *
     * @param string $purchaseEndPruchase
     * @return UserPermission
     */
    public function setPurchaseEndPruchase($purchaseEndPruchase)
    {
        $this->purchaseEndPruchase = $purchaseEndPruchase;

        return $this;
    }

    /**
     * Get purchaseEndPruchase
     *
     * @return string 
     */
    public function getPurchaseEndPruchase()
    {
        return $this->purchaseEndPruchase;
    }

    /**
     * Set purchasePruchases
     *
     * @param string $purchasePruchases
     * @return UserPermission
     */
    public function setPurchasePruchases($purchasePruchases)
    {
        $this->purchasePruchases = $purchasePruchases;

        return $this;
    }

    /**
     * Get purchasePruchases
     *
     * @return string 
     */
    public function getPurchasePruchases()
    {
        return $this->purchasePruchases;
    }

    /**
     * Set purchaseCardsPendency
     *
     * @param string $purchaseCardsPendency
     * @return UserPermission
     */
    public function setPurchaseCardsPendency($purchaseCardsPendency)
    {
        $this->purchaseCardsPendency = $purchaseCardsPendency;

        return $this;
    }

    /**
     * Get purchaseCardsPendency
     *
     * @return string 
     */
    public function getPurchaseCardsPendency()
    {
        return $this->purchaseCardsPendency;
    }

    /**
     * Set saleClients
     *
     * @param string $saleClients
     * @return UserPermission
     */
    public function setSaleClients($saleClients)
    {
        $this->saleClients = $saleClients;

        return $this;
    }

    /**
     * Get saleClients
     *
     * @return string 
     */
    public function getSaleClients()
    {
        return $this->saleClients;
    }

    /**
     * Set saleBalanceClients
     *
     * @param string $saleBalanceClients
     * @return UserPermission
     */
    public function setSaleBalanceClients($saleBalanceClients)
    {
        $this->saleBalanceClients = $saleBalanceClients;

        return $this;
    }

    /**
     * Get saleBalanceClients
     *
     * @return string 
     */
    public function getSaleBalanceClients()
    {
        return $this->saleBalanceClients;
    }

    /**
     * Set saleFutureBoardings
     *
     * @param string $saleFutureBoardings
     * @return UserPermission
     */
    public function setSaleFutureBoardings($saleFutureBoardings)
    {
        $this->saleFutureBoardings = $saleFutureBoardings;

        return $this;
    }

    /**
     * Get saleFutureBoardings
     *
     * @return string 
     */
    public function getSaleFutureBoardings()
    {
        return $this->saleFutureBoardings;
    }

    /**
     * Set saleRefundCancel
     *
     * @param string $saleRefundCancel
     * @return UserPermission
     */
    public function setSaleRefundCancel($saleRefundCancel)
    {
        $this->saleRefundCancel = $saleRefundCancel;

        return $this;
    }

    /**
     * Get saleRefundCancel
     *
     * @return string 
     */
    public function getSaleRefundCancel()
    {
        return $this->saleRefundCancel;
    }

    /**
     * Set saleRevertRefund
     *
     * @param string $saleRevertRefund
     * @return UserPermission
     */
    public function setSaleRevertRefund($saleRevertRefund)
    {
        $this->saleRevertRefund = $saleRevertRefund;

        return $this;
    }

    /**
     * Get saleRevertRefund
     *
     * @return string 
     */
    public function getSaleRevertRefund()
    {
        return $this->saleRevertRefund;
    }

    /**
     * Set user
     *
     * @param \Businesspartner $user
     * @return UserPermission
     */
    public function setUser(\Businesspartner $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Businesspartner 
     */
    public function getUser()
    {
        return $this->user;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="wizar_sale_event", type="string", length=5, nullable=false)
     */
    private $wizarSaleEvent = 'false';


    /**
     * Set wizarSaleEvent
     *
     * @param string $wizarSaleEvent
     * @return UserPermission
     */
    public function setWizarSaleEvent($wizarSaleEvent)
    {
        $this->wizarSaleEvent = $wizarSaleEvent;

        return $this;
    }

    /**
     * Get wizarSaleEvent
     *
     * @return string 
     */
    public function getWizarSaleEvent()
    {
        return $this->wizarSaleEvent;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="on_vacation", type="string", length=5, nullable=false)
     */
    private $onVacation;


    /**
     * Set onVacation
     *
     * @param string $onVacation
     * @return Businesspartner
     */
    public function setOnVacation($onVacation)
    {
        $this->onVacation = $onVacation;

        return $this;
    }

    /**
     * Get onVacation
     *
     * @return string 
     */
    public function getOnVacation()
    {
        return $this->onVacation;
    }

     /**
     * @var \DateTime
     *
     * @ORM\Column(name="vacation_end", type="datetime", nullable=true)
     */
    private $vacationEnd;


    /**
     * Set vacationEnd
     *
     * @param \DateTime $vacationEnd
     * @return Businesspartner
     */
    public function setVacationEnd($vacationEnd)
    {
        $this->vacationEnd = $vacationEnd;

        return $this;
    }

    /**
     * Get vacationEnd
     *
     * @return \DateTime 
     */
    public function getVacationEnd()
    {
        return $this->vacationEnd;
    }

    
    /**
     * @var string
     *
     * @ORM\Column(name="is_doze_trinta_e_seis", type="string", length=5, nullable=false)
     */
    private $isDozeTrintaESeis;


    /**
     * Set isDozeTrintaESeis
     *
     * @param string $isDozeTrintaESeis
     * @return Businesspartner
     */
    public function setIsDozeTrintaESeis($isDozeTrintaESeis)
    {
        $this->isDozeTrintaESeis = $isDozeTrintaESeis;

        return $this;
    }

    /**
     * Get isDozeTrintaESeis
     *
     * @return string 
     */
    public function getIsDozeTrintaESeis()
    {
        return $this->isDozeTrintaESeis;
    }
}
