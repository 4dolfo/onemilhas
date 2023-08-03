<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * SomeDataFutureClients
 *
 * @ORM\Table(name="some_data_future_clients")
 * @ORM\Entity
 */
class SomeDataFutureClients
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_days", type="string", length=8, nullable=true)
     */
    private $paymentDays;

    /**
     * @var string
     *
     * @ORM\Column(name="registration_code", type="string", length=32, nullable=true)
     */
    private $registrationCode;


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
     * @return SomeDataFutureClients
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
     * Set paymentDays
     *
     * @param string $paymentDays
     * @return SomeDataFutureClients
     */
    public function setPaymentDays($paymentDays)
    {
        $this->paymentDays = $paymentDays;

        return $this;
    }

    /**
     * Get paymentDays
     *
     * @return string 
     */
    public function getPaymentDays()
    {
        return $this->paymentDays;
    }

    /**
     * Set registrationCode
     *
     * @param string $registrationCode
     * @return SomeDataFutureClients
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
}
