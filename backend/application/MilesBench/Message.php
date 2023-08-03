<?php
namespace MilesBench;

/**
 * Description of Message
 *
 * @author tulio
 */
class Message {

    const ERROR = 'E';
    const SUCCESS = 'S';

    /**
     * @var string
     */
    private $type;

    /**
     *
     * @var string
     */
    private $text;

    /**
     * 
     * @return string
     */
    public function getType() {
        return $this->type;
    }

    /**
     * 
     * @param string $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * 
     * @return string
     */
    public function getText() {
        return $this->text;
    }

    /**
     * 
     * @param string $text
     */
    public function setText($text) {
        $this->text = $text;
    }

}