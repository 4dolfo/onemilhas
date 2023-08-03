<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * UserGroup
 *
 * @ORM\Table(name="user_group", indexes={@ORM\Index(name="user", columns={"user"})})
 * @ORM\Entity
 */
class UserGroup
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
     * @var boolean
     *
     * @ORM\Column(name="first_issue", type="boolean", nullable=false)
     */
    private $firstIssue = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="emission_track", type="boolean", nullable=false)
     */
    private $emissionTrack = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="limit_track", type="boolean", nullable=false)
     */
    private $limitTrack = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="future_boardings_track", type="boolean", nullable=false)
     */
    private $futureBoardingsTrack = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="difficult_contact_track", type="boolean", nullable=false)
     */
    private $difficultContactTrack = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="status_pending_release_track", type="boolean", nullable=false)
     */
    private $statusPendingReleaseTrack = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="status_credit_analysis_track", type="boolean", nullable=false)
     */
    private $statusCreditAnalysisTrack = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="cards_bloqueds", type="boolean", nullable=false)
     */
    private $cardsBloqueds = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="clients_track", type="boolean", nullable=false)
     */
    private $clientsTrack = '0';

    /**
     * @var \Businesspartner
     *
     * @ORM\ManyToOne(targetEntity="Businesspartner")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="id")
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
     * Set firstIssue
     *
     * @param boolean $firstIssue
     * @return UserGroup
     */
    public function setFirstIssue($firstIssue)
    {
        $this->firstIssue = $firstIssue;

        return $this;
    }

    /**
     * Get firstIssue
     *
     * @return boolean 
     */
    public function getFirstIssue()
    {
        return $this->firstIssue;
    }

    /**
     * Set emissionTrack
     *
     * @param boolean $emissionTrack
     * @return UserGroup
     */
    public function setEmissionTrack($emissionTrack)
    {
        $this->emissionTrack = $emissionTrack;

        return $this;
    }

    /**
     * Get emissionTrack
     *
     * @return boolean 
     */
    public function getEmissionTrack()
    {
        return $this->emissionTrack;
    }

    /**
     * Set limitTrack
     *
     * @param boolean $limitTrack
     * @return UserGroup
     */
    public function setLimitTrack($limitTrack)
    {
        $this->limitTrack = $limitTrack;

        return $this;
    }

    /**
     * Get limitTrack
     *
     * @return boolean 
     */
    public function getLimitTrack()
    {
        return $this->limitTrack;
    }

    /**
     * Set futureBoardingsTrack
     *
     * @param boolean $futureBoardingsTrack
     * @return UserGroup
     */
    public function setFutureBoardingsTrack($futureBoardingsTrack)
    {
        $this->futureBoardingsTrack = $futureBoardingsTrack;

        return $this;
    }

    /**
     * Get futureBoardingsTrack
     *
     * @return boolean 
     */
    public function getFutureBoardingsTrack()
    {
        return $this->futureBoardingsTrack;
    }

    /**
     * Set difficultContactTrack
     *
     * @param boolean $difficultContactTrack
     * @return UserGroup
     */
    public function setDifficultContactTrack($difficultContactTrack)
    {
        $this->difficultContactTrack = $difficultContactTrack;

        return $this;
    }

    /**
     * Get difficultContactTrack
     *
     * @return boolean 
     */
    public function getDifficultContactTrack()
    {
        return $this->difficultContactTrack;
    }

    /**
     * Set statusPendingReleaseTrack
     *
     * @param boolean $statusPendingReleaseTrack
     * @return UserGroup
     */
    public function setStatusPendingReleaseTrack($statusPendingReleaseTrack)
    {
        $this->statusPendingReleaseTrack = $statusPendingReleaseTrack;

        return $this;
    }

    /**
     * Get statusPendingReleaseTrack
     *
     * @return boolean 
     */
    public function getStatusPendingReleaseTrack()
    {
        return $this->statusPendingReleaseTrack;
    }

    /**
     * Set statusCreditAnalysisTrack
     *
     * @param boolean $statusCreditAnalysisTrack
     * @return UserGroup
     */
    public function setStatusCreditAnalysisTrack($statusCreditAnalysisTrack)
    {
        $this->statusCreditAnalysisTrack = $statusCreditAnalysisTrack;

        return $this;
    }

    /**
     * Get statusCreditAnalysisTrack
     *
     * @return boolean 
     */
    public function getStatusCreditAnalysisTrack()
    {
        return $this->statusCreditAnalysisTrack;
    }

    /**
     * Set cardsBloqueds
     *
     * @param boolean $cardsBloqueds
     * @return UserGroup
     */
    public function setCardsBloqueds($cardsBloqueds)
    {
        $this->cardsBloqueds = $cardsBloqueds;

        return $this;
    }

    /**
     * Get cardsBloqueds
     *
     * @return boolean 
     */
    public function getCardsBloqueds()
    {
        return $this->cardsBloqueds;
    }

    /**
     * Set clientsTrack
     *
     * @param boolean $clientsTrack
     * @return UserGroup
     */
    public function setClientsTrack($clientsTrack)
    {
        $this->clientsTrack = $clientsTrack;

        return $this;
    }

    /**
     * Get clientsTrack
     *
     * @return boolean 
     */
    public function getClientsTrack()
    {
        return $this->clientsTrack;
    }

    /**
     * Set user
     *
     * @param \Businesspartner $user
     * @return UserGroup
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
}
