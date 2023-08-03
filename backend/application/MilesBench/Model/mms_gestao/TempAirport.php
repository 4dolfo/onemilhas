<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * TempAirport
 *
 * @ORM\Table(name="temp_airport")
 * @ORM\Entity
 */
class TempAirport
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
     * @ORM\Column(name="airport", type="string", length=100, nullable=true)
     */
    private $airport;

    /**
     * @var string
     *
     * @ORM\Column(name="newName", type="string", length=41, nullable=true)
     */
    private $newname;

    /**
     * @var string
     *
     * @ORM\Column(name="initials", type="string", length=45, nullable=true)
     */
    private $initials;

    /**
     * @var string
     *
     * @ORM\Column(name="newInitials", type="string", length=32, nullable=true)
     */
    private $newinitials;


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
     * Set airport
     *
     * @param string $airport
     * @return TempAirport
     */
    public function setAirport($airport)
    {
        $this->airport = $airport;

        return $this;
    }

    /**
     * Get airport
     *
     * @return string 
     */
    public function getAirport()
    {
        return $this->airport;
    }

    /**
     * Set newname
     *
     * @param string $newname
     * @return TempAirport
     */
    public function setNewname($newname)
    {
        $this->newname = $newname;

        return $this;
    }

    /**
     * Get newname
     *
     * @return string 
     */
    public function getNewname()
    {
        return $this->newname;
    }

    /**
     * Set initials
     *
     * @param string $initials
     * @return TempAirport
     */
    public function setInitials($initials)
    {
        $this->initials = $initials;

        return $this;
    }

    /**
     * Get initials
     *
     * @return string 
     */
    public function getInitials()
    {
        return $this->initials;
    }

    /**
     * Set newinitials
     *
     * @param string $newinitials
     * @return TempAirport
     */
    public function setNewinitials($newinitials)
    {
        $this->newinitials = $newinitials;

        return $this;
    }

    /**
     * Get newinitials
     *
     * @return string 
     */
    public function getNewinitials()
    {
        return $this->newinitials;
    }
}
