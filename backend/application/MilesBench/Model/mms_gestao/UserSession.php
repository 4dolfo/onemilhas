<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * UserSession
 *
 * @ORM\Table(name="user_session")
 * @ORM\Entity
 */
class UserSession
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
     * @ORM\Column(name="email", type="string", length=200, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="hashId", type="string", length=1024, nullable=false)
     */
    private $hashid;


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
     * Set email
     *
     * @param string $email
     * @return UserSession
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
     * Set hashid
     *
     * @param string $hashid
     * @return UserSession
     */
    public function setHashid($hashid)
    {
        $this->hashid = $hashid;

        return $this;
    }

    /**
     * Get hashid
     *
     * @return string 
     */
    public function getHashid()
    {
        return $this->hashid;
    }
}
