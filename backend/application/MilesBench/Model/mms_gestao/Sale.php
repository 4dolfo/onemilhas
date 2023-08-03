<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * Sale
 *
 * @ORM\Table(name="sale", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})}, indexes={@ORM\Index(name="fk_sale_businesspartner1_idx", columns={"pax_id"}), @ORM\Index(name="fk_sale_businesspartner2_idx", columns={"client_id"}), @ORM\Index(name="fk_sale_airline1_idx", columns={"airline_id"}), @ORM\Index(name="fk_sale_Cards1_idx", columns={"cards_id"}), @ORM\Index(name="fk_sale_airport1_idx", columns={"airport_from"}), @ORM\Index(name="fk_sale_airport2_idx", columns={"airport_to"}), @ORM\Index(name="fk_sale_businesspartner3", columns={"partner_id"}), @ORM\Index(name="provider_sale_by_third", columns={"provider_sale_by_third"}), @ORM\Index(name="issuing_id", columns={"issuing_id"}), @ORM\Index(name="card_tax", columns={"card_tax"}), @ORM\Index(name="user_id", columns={"user_id"}), @ORM\Index(name="purchase_id", columns={"purchase_id"}), @ORM\Index(name="online_pax_id", columns={"online_pax_id"})})
 * @ORM\Entity
 */
class Sale
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
     * @ORM\Column(name="flight_locator", type="string", length=45, nullable=true)
     */
    private $flightLocator;

    /**
     * @var string
     *
     * @ORM\Column(name="checkin_state", type="string", length=20, nullable=true)
     */
    private $checkinState;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="issue_date", type="datetime", nullable=false)
     */
    private $issueDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="boarding_date", type="datetime", nullable=true)
     */
    private $boardingDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="return_date", type="datetime", nullable=true)
     */
    private $returnDate;

    /**
     * @var string
     *
     * @ORM\Column(name="miles_used", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $milesUsed = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="tax", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $tax = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="amount_paid", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $amountPaid = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="total_cost", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $totalCost = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="kickback", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $kickback = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=2000, nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=30, nullable=true)
     */
    private $status;

    /**
     * @var string
     *
     * @ORM\Column(name="flight", type="string", length=45, nullable=true)
     */
    private $flight;

    /**
     * @var string
     *
     * @ORM\Column(name="flight_hour", type="string", length=45, nullable=true)
     */
    private $flightHour;

    /**
     * @var string
     *
     * @ORM\Column(name="extra_fee", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $extraFee;

    /**
     * @var string
     *
     * @ORM\Column(name="du_tax", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $duTax;

    /**
     * @var string
     *
     * @ORM\Column(name="external_id", type="string", length=50, nullable=true)
     */
    private $externalId;

    /**
     * @var integer
     *
     * @ORM\Column(name="online_flight_id", type="integer", nullable=true)
     */
    private $onlineFlightId;

    /**
     * @var string
     *
     * @ORM\Column(name="miles_original", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $milesOriginal = '0.00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="landing_date", type="datetime", nullable=false)
     */
    private $landingDate;

    /**
     * @var string
     *
     * @ORM\Column(name="ticket_code", type="string", length=15, nullable=true)
     */
    private $ticketCode;

    /**
     * @var string
     *
     * @ORM\Column(name="reservation_code", type="string", length=15, nullable=true)
     */
    private $reservationCode;

    /**
     * @var string
     *
     * @ORM\Column(name="miles_money", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $milesMoney = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="sale_by_third", type="string", length=1, nullable=true)
     */
    private $saleByThird = 'N';

    /**
     * @var string
     *
     * @ORM\Column(name="processing_time", type="string", length=5, nullable=true)
     */
    private $processingTime;

    /**
     * @var string
     *
     * @ORM\Column(name="sale_type", type="string", length=8, nullable=true)
     */
    private $saleType;

    /**
     * @var string
     *
     * @ORM\Column(name="discount", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $discount = '0.00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="refund_date", type="datetime", nullable=true)
     */
    private $refundDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="checkin_date", type="datetime", nullable=true)
     */
    private $checkinDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="occurrence_date", type="datetime", nullable=true)
     */
    private $occurrenceDate;

    /**
     * @var string
     *
     * @ORM\Column(name="occurrence_status", type="string", length=16, nullable=true)
     */
    private $occurrenceStatus;

    /**
     * @var string
     *
     * @ORM\Column(name="points_waiting", type="decimal", precision=20, scale=2, nullable=false)
     */
    private $pointsWaiting = '0.00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="airline_solicitation", type="datetime", nullable=true)
     */
    private $airlineSolicitation;

    /**
     * @var string
     *
     * @ORM\Column(name="sale_checked", type="string", length=5, nullable=false)
     */
    private $saleChecked = 'false';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sale_checked_date", type="datetime", nullable=true)
     */
    private $saleCheckedDate;

    /**
     * @var string
     *
     * @ORM\Column(name="early_covered", type="string", length=5, nullable=false)
     */
    private $earlyCovered = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="partner_sms", type="string", length=5, nullable=true)
     */
    private $partnerSms = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="refund_checked", type="string", length=5, nullable=false)
     */
    private $refundChecked = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="repricing_checked", type="string", length=5, nullable=false)
     */
    private $repricingChecked = 'false';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="repricing_date", type="datetime", nullable=true)
     */
    private $repricingDate;

    /**
     * @var string
     *
     * @ORM\Column(name="seat", type="string", length=6, nullable=false)
     */
    private $seat = '';

    /**
     * @var string
     *
     * @ORM\Column(name="is_pendency", type="string", length=5, nullable=false)
     */
    private $isPendency = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="tax_billet", type="decimal", precision=20, scale=2, nullable=true)
     */
    private $taxBillet = '0.00';

    /**
     * @var string
     *
     * @ORM\Column(name="baggage", type="string", length=5, nullable=true)
     */
    private $baggage;

    /**
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=15, nullable=true)
     */
    private $class;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sale_checked_date_2", type="datetime", nullable=true)
     */
    private $saleCheckedDate2;

    /**
     * @var string
     *
     * @ORM\Column(name="sale_checked_2", type="string", length=5, nullable=true)
     */
    private $saleChecked2 = 'false';

    /**
     * @var \OnlinePax
     *
     * @ORM\ManyToOne(targetEntity="OnlinePax")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="online_pax_id", referencedColumnName="id")
     * })
     */
    private $onlinePax;

    /**
     * @var \Airline
     *
     * @ORM\ManyToOne(targetEntity="Airline")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="airline_id", referencedColumnName="id")
     * })
     */
    private $airline;

    /**
     * @var \Airport
     *
     * @ORM\ManyToOne(targetEntity="Airport")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="airport_from", referencedColumnName="id")
     * })
     */
    private $airportFrom;

    /**
     * @var \Airport
     *
     * @ORM\ManyToOne(targetEntity="Airport")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="airport_to", referencedColumnName="id")
     * })
     */
    private $airportTo;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="pax_id", referencedColumnName="id")
     * })
     */
    private $pax;

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
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="partner_id", referencedColumnName="id")
     * })
     */
    private $partner;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="provider_sale_by_third", referencedColumnName="id")
     * })
     */
    private $providerSaleByThird;

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
     * @var \Cards
     *
     * @ORM\ManyToOne(targetEntity="Cards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="cards_id", referencedColumnName="id")
     * })
     */
    private $cards;

    /**
     * @var \InternalCards
     *
     * @ORM\ManyToOne(targetEntity="InternalCards")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="card_tax", referencedColumnName="id")
     * })
     */
    private $cardTax;

    /**
     * @var \Purchase
     *
     * @ORM\ManyToOne(targetEntity="Purchase")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="purchase_id", referencedColumnName="id")
     * })
     */
    private $purchase;

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="issuing_id", referencedColumnName="id")
     * })
     */
    private $issuing;


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
     * Set flightLocator
     *
     * @param string $flightLocator
     * @return Sale
     */
    public function setFlightLocator($flightLocator)
    {
        $this->flightLocator = $flightLocator;

        return $this;
    }

    /**
     * Get flightLocator
     *
     * @return string 
     */
    public function getFlightLocator()
    {
        return $this->flightLocator;
    }

    /**
     * Set checkinState
     *
     * @param string $checkinState
     * @return Sale
     */
    public function setCheckinState($checkinState)
    {
        $this->checkinState = $checkinState;

        return $this;
    }

    /**
     * Get checkinState
     *
     * @return string 
     */
    public function getCheckinState()
    {
        return $this->checkinState;
    }

    /**
     * Set issueDate
     *
     * @param \DateTime $issueDate
     * @return Sale
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
     * Set boardingDate
     *
     * @param \DateTime $boardingDate
     * @return Sale
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
     * Set returnDate
     *
     * @param \DateTime $returnDate
     * @return Sale
     */
    public function setReturnDate($returnDate)
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    /**
     * Get returnDate
     *
     * @return \DateTime 
     */
    public function getReturnDate()
    {
        return $this->returnDate;
    }

    /**
     * Set milesUsed
     *
     * @param string $milesUsed
     * @return Sale
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
     * Set tax
     *
     * @param string $tax
     * @return Sale
     */
    public function setTax($tax)
    {
        $this->tax = $tax;

        return $this;
    }

    /**
     * Get tax
     *
     * @return string 
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Set amountPaid
     *
     * @param string $amountPaid
     * @return Sale
     */
    public function setAmountPaid($amountPaid)
    {
        $this->amountPaid = $amountPaid;

        return $this;
    }

    /**
     * Get amountPaid
     *
     * @return string 
     */
    public function getAmountPaid()
    {
        return $this->amountPaid;
    }

    /**
     * Set totalCost
     *
     * @param string $totalCost
     * @return Sale
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
     * Set kickback
     *
     * @param string $kickback
     * @return Sale
     */
    public function setKickback($kickback)
    {
        $this->kickback = $kickback;

        return $this;
    }

    /**
     * Get kickback
     *
     * @return string 
     */
    public function getKickback()
    {
        return $this->kickback;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return Sale
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
     * Set status
     *
     * @param string $status
     * @return Sale
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
     * Set flight
     *
     * @param string $flight
     * @return Sale
     */
    public function setFlight($flight)
    {
        $this->flight = $flight;

        return $this;
    }

    /**
     * Get flight
     *
     * @return string 
     */
    public function getFlight()
    {
        return $this->flight;
    }

    /**
     * Set flightHour
     *
     * @param string $flightHour
     * @return Sale
     */
    public function setFlightHour($flightHour)
    {
        $this->flightHour = $flightHour;

        return $this;
    }

    /**
     * Get flightHour
     *
     * @return string 
     */
    public function getFlightHour()
    {
        return $this->flightHour;
    }

    /**
     * Set extraFee
     *
     * @param string $extraFee
     * @return Sale
     */
    public function setExtraFee($extraFee)
    {
        $this->extraFee = $extraFee;

        return $this;
    }

    /**
     * Get extraFee
     *
     * @return string 
     */
    public function getExtraFee()
    {
        return $this->extraFee;
    }

    /**
     * Set duTax
     *
     * @param string $duTax
     * @return Sale
     */
    public function setDuTax($duTax)
    {
        $this->duTax = $duTax;

        return $this;
    }

    /**
     * Get duTax
     *
     * @return string 
     */
    public function getDuTax()
    {
        return $this->duTax;
    }

    /**
     * Set externalId
     *
     * @param string $externalId
     * @return Sale
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
     * Set onlineFlightId
     *
     * @param integer $onlineFlightId
     * @return Sale
     */
    public function setOnlineFlightId($onlineFlightId)
    {
        $this->onlineFlightId = $onlineFlightId;

        return $this;
    }

    /**
     * Get onlineFlightId
     *
     * @return integer 
     */
    public function getOnlineFlightId()
    {
        return $this->onlineFlightId;
    }

    /**
     * Set milesOriginal
     *
     * @param string $milesOriginal
     * @return Sale
     */
    public function setMilesOriginal($milesOriginal)
    {
        $this->milesOriginal = $milesOriginal;

        return $this;
    }

    /**
     * Get milesOriginal
     *
     * @return string 
     */
    public function getMilesOriginal()
    {
        return $this->milesOriginal;
    }

    /**
     * Set landingDate
     *
     * @param \DateTime $landingDate
     * @return Sale
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
     * Set ticketCode
     *
     * @param string $ticketCode
     * @return Sale
     */
    public function setTicketCode($ticketCode)
    {
        $this->ticketCode = $ticketCode;

        return $this;
    }

    /**
     * Get ticketCode
     *
     * @return string 
     */
    public function getTicketCode()
    {
        return $this->ticketCode;
    }

    /**
     * Set reservationCode
     *
     * @param string $reservationCode
     * @return Sale
     */
    public function setReservationCode($reservationCode)
    {
        $this->reservationCode = $reservationCode;

        return $this;
    }

    /**
     * Get reservationCode
     *
     * @return string 
     */
    public function getReservationCode()
    {
        return $this->reservationCode;
    }

    /**
     * Set milesMoney
     *
     * @param string $milesMoney
     * @return Sale
     */
    public function setMilesMoney($milesMoney)
    {
        $this->milesMoney = $milesMoney;

        return $this;
    }

    /**
     * Get milesMoney
     *
     * @return string 
     */
    public function getMilesMoney()
    {
        return $this->milesMoney;
    }

    /**
     * Set saleByThird
     *
     * @param string $saleByThird
     * @return Sale
     */
    public function setSaleByThird($saleByThird)
    {
        $this->saleByThird = $saleByThird;

        return $this;
    }

    /**
     * Get saleByThird
     *
     * @return string 
     */
    public function getSaleByThird()
    {
        return $this->saleByThird;
    }

    /**
     * Set processingTime
     *
     * @param string $processingTime
     * @return Sale
     */
    public function setProcessingTime($processingTime)
    {
        $this->processingTime = $processingTime;

        return $this;
    }

    /**
     * Get processingTime
     *
     * @return string 
     */
    public function getProcessingTime()
    {
        return $this->processingTime;
    }

    /**
     * Set saleType
     *
     * @param string $saleType
     * @return Sale
     */
    public function setSaleType($saleType)
    {
        $this->saleType = $saleType;

        return $this;
    }

    /**
     * Get saleType
     *
     * @return string 
     */
    public function getSaleType()
    {
        return $this->saleType;
    }

    /**
     * Set discount
     *
     * @param string $discount
     * @return Sale
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Get discount
     *
     * @return string 
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set refundDate
     *
     * @param \DateTime $refundDate
     * @return Sale
     */
    public function setRefundDate($refundDate)
    {
        $this->refundDate = $refundDate;

        return $this;
    }

    /**
     * Get refundDate
     *
     * @return \DateTime 
     */
    public function getRefundDate()
    {
        return $this->refundDate;
    }

    /**
     * Set checkinDate
     *
     * @param \DateTime $checkinDate
     * @return Sale
     */
    public function setCheckinDate($checkinDate)
    {
        $this->checkinDate = $checkinDate;

        return $this;
    }

    /**
     * Get checkinDate
     *
     * @return \DateTime 
     */
    public function getCheckinDate()
    {
        return $this->checkinDate;
    }

    /**
     * Set occurrenceDate
     *
     * @param \DateTime $occurrenceDate
     * @return Sale
     */
    public function setOccurrenceDate($occurrenceDate)
    {
        $this->occurrenceDate = $occurrenceDate;

        return $this;
    }

    /**
     * Get occurrenceDate
     *
     * @return \DateTime 
     */
    public function getOccurrenceDate()
    {
        return $this->occurrenceDate;
    }

    /**
     * Set occurrenceStatus
     *
     * @param string $occurrenceStatus
     * @return Sale
     */
    public function setOccurrenceStatus($occurrenceStatus)
    {
        $this->occurrenceStatus = $occurrenceStatus;

        return $this;
    }

    /**
     * Get occurrenceStatus
     *
     * @return string 
     */
    public function getOccurrenceStatus()
    {
        return $this->occurrenceStatus;
    }

    /**
     * Set pointsWaiting
     *
     * @param string $pointsWaiting
     * @return Sale
     */
    public function setPointsWaiting($pointsWaiting)
    {
        $this->pointsWaiting = $pointsWaiting;

        return $this;
    }

    /**
     * Get pointsWaiting
     *
     * @return string 
     */
    public function getPointsWaiting()
    {
        return $this->pointsWaiting;
    }

    /**
     * Set airlineSolicitation
     *
     * @param \DateTime $airlineSolicitation
     * @return Sale
     */
    public function setAirlineSolicitation($airlineSolicitation)
    {
        $this->airlineSolicitation = $airlineSolicitation;

        return $this;
    }

    /**
     * Get airlineSolicitation
     *
     * @return \DateTime 
     */
    public function getAirlineSolicitation()
    {
        return $this->airlineSolicitation;
    }

    /**
     * Set saleChecked
     *
     * @param string $saleChecked
     * @return Sale
     */
    public function setSaleChecked($saleChecked)
    {
        $this->saleChecked = $saleChecked;

        return $this;
    }

    /**
     * Get saleChecked
     *
     * @return string 
     */
    public function getSaleChecked()
    {
        return $this->saleChecked;
    }

    /**
     * Set saleCheckedDate
     *
     * @param \DateTime $saleCheckedDate
     * @return Sale
     */
    public function setSaleCheckedDate($saleCheckedDate)
    {
        $this->saleCheckedDate = $saleCheckedDate;

        return $this;
    }

    /**
     * Get saleCheckedDate
     *
     * @return \DateTime 
     */
    public function getSaleCheckedDate()
    {
        return $this->saleCheckedDate;
    }

    /**
     * Set earlyCovered
     *
     * @param string $earlyCovered
     * @return Sale
     */
    public function setEarlyCovered($earlyCovered)
    {
        $this->earlyCovered = $earlyCovered;

        return $this;
    }

    /**
     * Get earlyCovered
     *
     * @return string 
     */
    public function getEarlyCovered()
    {
        return $this->earlyCovered;
    }

    /**
     * Set partnerSms
     *
     * @param string $partnerSms
     * @return Sale
     */
    public function setPartnerSms($partnerSms)
    {
        $this->partnerSms = $partnerSms;

        return $this;
    }

    /**
     * Get partnerSms
     *
     * @return string 
     */
    public function getPartnerSms()
    {
        return $this->partnerSms;
    }

    /**
     * Set refundChecked
     *
     * @param string $refundChecked
     * @return Sale
     */
    public function setRefundChecked($refundChecked)
    {
        $this->refundChecked = $refundChecked;

        return $this;
    }

    /**
     * Get refundChecked
     *
     * @return string 
     */
    public function getRefundChecked()
    {
        return $this->refundChecked;
    }

    /**
     * Set repricingChecked
     *
     * @param string $repricingChecked
     * @return Sale
     */
    public function setRepricingChecked($repricingChecked)
    {
        $this->repricingChecked = $repricingChecked;

        return $this;
    }

    /**
     * Get repricingChecked
     *
     * @return string 
     */
    public function getRepricingChecked()
    {
        return $this->repricingChecked;
    }

    /**
     * Set repricingDate
     *
     * @param \DateTime $repricingDate
     * @return Sale
     */
    public function setRepricingDate($repricingDate)
    {
        $this->repricingDate = $repricingDate;

        return $this;
    }

    /**
     * Get repricingDate
     *
     * @return \DateTime 
     */
    public function getRepricingDate()
    {
        return $this->repricingDate;
    }

    /**
     * Set seat
     *
     * @param string $seat
     * @return Sale
     */
    public function setSeat($seat)
    {
        $this->seat = $seat;

        return $this;
    }

    /**
     * Get seat
     *
     * @return string 
     */
    public function getSeat()
    {
        return $this->seat;
    }

    /**
     * Set isPendency
     *
     * @param string $isPendency
     * @return Sale
     */
    public function setIsPendency($isPendency)
    {
        $this->isPendency = $isPendency;

        return $this;
    }

    /**
     * Get isPendency
     *
     * @return string 
     */
    public function getIsPendency()
    {
        return $this->isPendency;
    }

    /**
     * Set taxBillet
     *
     * @param string $taxBillet
     * @return Sale
     */
    public function setTaxBillet($taxBillet)
    {
        $this->taxBillet = $taxBillet;

        return $this;
    }

    /**
     * Get taxBillet
     *
     * @return string 
     */
    public function getTaxBillet()
    {
        return $this->taxBillet;
    }

    /**
     * Set baggage
     *
     * @param string $baggage
     * @return Sale
     */
    public function setBaggage($baggage)
    {
        $this->baggage = $baggage;

        return $this;
    }

    /**
     * Get baggage
     *
     * @return string 
     */
    public function getBaggage()
    {
        return $this->baggage;
    }

    /**
     * Set class
     *
     * @param string $class
     * @return Sale
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class
     *
     * @return string 
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set saleCheckedDate2
     *
     * @param \DateTime $saleCheckedDate2
     * @return Sale
     */
    public function setSaleCheckedDate2($saleCheckedDate2)
    {
        $this->saleCheckedDate2 = $saleCheckedDate2;

        return $this;
    }

    /**
     * Get saleCheckedDate2
     *
     * @return \DateTime 
     */
    public function getSaleCheckedDate2()
    {
        return $this->saleCheckedDate2;
    }

    /**
     * Set saleChecked2
     *
     * @param string $saleChecked2
     * @return Sale
     */
    public function setSaleChecked2($saleChecked2)
    {
        $this->saleChecked2 = $saleChecked2;

        return $this;
    }

    /**
     * Get saleChecked2
     *
     * @return string 
     */
    public function getSaleChecked2()
    {
        return $this->saleChecked2;
    }

    /**
     * Set onlinePax
     *
     * @param \OnlinePax $onlinePax
     * @return Sale
     */
    public function setOnlinePax(\OnlinePax $onlinePax = null)
    {
        $this->onlinePax = $onlinePax;

        return $this;
    }

    /**
     * Get onlinePax
     *
     * @return \OnlinePax 
     */
    public function getOnlinePax()
    {
        return $this->onlinePax;
    }

    /**
     * Set airline
     *
     * @param \Airline $airline
     * @return Sale
     */
    public function setAirline(\Airline $airline = null)
    {
        $this->airline = $airline;

        return $this;
    }

    /**
     * Get airline
     *
     * @return \Airline 
     */
    public function getAirline()
    {
        return $this->airline;
    }

    /**
     * Set airportFrom
     *
     * @param \Airport $airportFrom
     * @return Sale
     */
    public function setAirportFrom(\Airport $airportFrom = null)
    {
        $this->airportFrom = $airportFrom;

        return $this;
    }

    /**
     * Get airportFrom
     *
     * @return \Airport 
     */
    public function getAirportFrom()
    {
        return $this->airportFrom;
    }

    /**
     * Set airportTo
     *
     * @param \Airport $airportTo
     * @return Sale
     */
    public function setAirportTo(\Airport $airportTo = null)
    {
        $this->airportTo = $airportTo;

        return $this;
    }

    /**
     * Get airportTo
     *
     * @return \Airport 
     */
    public function getAirportTo()
    {
        return $this->airportTo;
    }

    /**
     * Set pax
     *
     * @param \Businesspartner $pax
     * @return Sale
     */
    public function setPax(\Businesspartner $pax = null)
    {
        $this->pax = $pax;

        return $this;
    }

    /**
     * Get pax
     *
     * @return \Businesspartner 
     */
    public function getPax()
    {
        return $this->pax;
    }

    /**
     * Set client
     *
     * @param \Businesspartner $client
     * @return Sale
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
     * Set partner
     *
     * @param \Businesspartner $partner
     * @return Sale
     */
    public function setPartner(\Businesspartner $partner = null)
    {
        $this->partner = $partner;

        return $this;
    }

    /**
     * Get partner
     *
     * @return \Businesspartner 
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * Set providerSaleByThird
     *
     * @param \Businesspartner $providerSaleByThird
     * @return Sale
     */
    public function setProviderSaleByThird(\Businesspartner $providerSaleByThird = null)
    {
        $this->providerSaleByThird = $providerSaleByThird;

        return $this;
    }

    /**
     * Get providerSaleByThird
     *
     * @return \Businesspartner 
     */
    public function getProviderSaleByThird()
    {
        return $this->providerSaleByThird;
    }

    /**
     * Set user
     *
     * @param \Businesspartner $user
     * @return Sale
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
     * Set cards
     *
     * @param \Cards $cards
     * @return Sale
     */
    public function setCards(\Cards $cards = null)
    {
        $this->cards = $cards;

        return $this;
    }

    /**
     * Get cards
     *
     * @return \Cards 
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Set cardTax
     *
     * @param \InternalCards $cardTax
     * @return Sale
     */
    public function setCardTax(\InternalCards $cardTax = null)
    {
        $this->cardTax = $cardTax;

        return $this;
    }

    /**
     * Get cardTax
     *
     * @return \InternalCards 
     */
    public function getCardTax()
    {
        return $this->cardTax;
    }

    /**
     * Set purchase
     *
     * @param \Purchase $purchase
     * @return Sale
     */
    public function setPurchase(\Purchase $purchase = null)
    {
        $this->purchase = $purchase;

        return $this;
    }

    /**
     * Get purchase
     *
     * @return \Purchase 
     */
    public function getPurchase()
    {
        return $this->purchase;
    }

    /**
     * Set issuing
     *
     * @param \Businesspartner $issuing
     * @return Sale
     */
    public function setIssuing(\Businesspartner $issuing = null)
    {
        $this->issuing = $issuing;

        return $this;
    }

    /**
     * Get issuing
     *
     * @return \Businesspartner 
     */
    public function getIssuing()
    {
        return $this->issuing;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="tax_online_payment", type="float", precision=20, scale=2, nullable=true)
     */
    private $taxOnlinePayment = '0.00';

    /**
     * @var float
     *
     * @ORM\Column(name="tax_online_validation", type="float", precision=20, scale=2, nullable=true)
     */
    private $taxOnlineValidation = '0.00';


    /**
     * Set taxOnlinePayment
     *
     * @param float $taxOnlinePayment
     * @return Sale
     */
    public function setTaxOnlinePayment($taxOnlinePayment)
    {
        $this->taxOnlinePayment = $taxOnlinePayment;

        return $this;
    }

    /**
     * Get taxOnlinePayment
     *
     * @return float 
     */
    public function getTaxOnlinePayment()
    {
        return $this->taxOnlinePayment;
    }

    /**
     * Set taxOnlineValidation
     *
     * @param float $taxOnlineValidation
     * @return Sale
     */
    public function setTaxOnlineValidation($taxOnlineValidation)
    {
        $this->taxOnlineValidation = $taxOnlineValidation;

        return $this;
    }

    /**
     * Get taxOnlineValidation
     *
     * @return float 
     */
    public function getTaxOnlineValidation()
    {
        return $this->taxOnlineValidation;
    }
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="processing_start_date", type="datetime", nullable=true)
     */
    private $processingStartDate;


    /**
     * Set processingStartDate
     *
     * @param \DateTime $processingStartDate
     * @return Sale
     */
    public function setProcessingStartDate($processingStartDate)
    {
        $this->processingStartDate = $processingStartDate;

        return $this;
    }

    /**
     * Get processingStartDate
     *
     * @return \DateTime 
     */
    public function getProcessingStartDate()
    {
        return $this->processingStartDate;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="special_seat", type="float", precision=20, scale=2, nullable=true)
     */
    private $specialSeat = '0.00';


    /**
     * Set specialSeat
     *
     * @param float $specialSeat
     * @return Sale
     */
    public function setSpecialSeat($specialSeat)
    {
        $this->specialSeat = $specialSeat;

        return $this;
    }

    /**
     * Get specialSeat
     *
     * @return float 
     */
    public function getSpecialSeat()
    {
        return $this->specialSeat;
    }
    /**
     * @var float
     *
     * @ORM\Column(name="baggage_price", type="float", precision=20, scale=2, nullable=true)
     */
    private $baggagePrice = '0.00';


    /**
     * Set baggagePrice
     *
     * @param float $baggagePrice
     * @return Sale
     */
    public function setBaggagePrice($baggagePrice)
    {
        $this->baggagePrice = $baggagePrice;

        return $this;
    }

    /**
     * Get baggagePrice
     *
     * @return float 
     */
    public function getBaggagePrice()
    {
        return $this->baggagePrice;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="is_extra", type="string", length=5, nullable=true)
     */
    private $isExtra = 'false';


    /**
     * Set isExtra
     *
     * @param string $isExtra
     * @return Sale
     */
    public function setIsExtra($isExtra)
    {
        $this->isExtra = $isExtra;

        return $this;
    }

    /**
     * Get isExtra
     *
     * @return string 
     */
    public function getIsExtra()
    {
        return $this->isExtra;
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
     * @return Sale
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
     * @ORM\Column(name="payment_method", type="string", length=255, nullable=true)
     */
    private $paymentMethod;


    /**
     * Set paymentMethod
     *
     * @param string $paymentMethod
     * @return Sale
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
     * @var string
     *
     * @ORM\Column(name="cancellation_requested", type="string", length=5, nullable=true)
     */
    private $cancellationRequested = 'false';

    /**
     * @var string
     *
     * @ORM\Column(name="refund_requested", type="string", length=5, nullable=true)
     */
    private $refundRequested = 'false';


    /**
     * Set cancellationRequested
     *
     * @param string $cancellationRequested
     * @return Sale
     */
    public function setCancellationRequested($cancellationRequested)
    {
        $this->cancellationRequested = $cancellationRequested;

        return $this;
    }

    /**
     * Get cancellationRequested
     *
     * @return string 
     */
    public function getCancellationRequested()
    {
        return $this->cancellationRequested;
    }

    /**
     * Set refundRequested
     *
     * @param string $refundRequested
     * @return Sale
     */
    public function setRefundRequested($refundRequested)
    {
        $this->refundRequested = $refundRequested;

        return $this;
    }

    /**
     * Get refundRequested
     *
     * @return string 
     */
    public function getRefundRequested()
    {
        return $this->refundRequested;
    }
    /**
     * @var boolean
     *
     * @ORM\Column(name="is_diamond", type="boolean", nullable=true)
     */
    private $isDiamond = '0';


    /**
     * Set isDiamond
     *
     * @param boolean $isDiamond
     * @return Sale
     */
    public function setIsDiamond($isDiamond)
    {
        $this->isDiamond = $isDiamond;

        return $this;
    }

    /**
     * Get isDiamond
     *
     * @return boolean 
     */
    public function getIsDiamond()
    {
        return $this->isDiamond;
    }
}
