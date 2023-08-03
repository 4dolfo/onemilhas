<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * CreditAnalysis
 *
 * @ORM\Table(name="credit_analysis", indexes={@ORM\Index(name="client_id", columns={"client_id"})})
 * @ORM\Entity
 */
class CreditAnalysis
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
     * @ORM\Column(name="score", type="string", length=15, nullable=true)
     */
    private $score;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_code_check", type="string", length=15, nullable=true)
     */
    private $registrationCodeCheck;

    /**
     * @var string
     *
     * @ORM\Column(name="adress_check", type="string", length=15, nullable=true)
     */
    private $adressCheck;

    /**
     * @var string
     *
     * @ORM\Column(name="credit_description", type="string", length=250, nullable=true)
     */
    private $creditDescription;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set score
     *
     * @param string $score
     * @return CreditAnalysis
     */
    public function setScore($score)
    {
        $this->score = $score;

        return $this;
    }

    /**
     * Get score
     *
     * @return string 
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set registrationCodeCheck
     *
     * @param string $registrationCodeCheck
     * @return CreditAnalysis
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
     * @return CreditAnalysis
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
     * @return CreditAnalysis
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
     * Set client
     *
     * @param \Businesspartner $client
     * @return CreditAnalysis
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
}
