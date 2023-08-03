<?php
namespace MilesBench\Request;

/**
 * Description of Response
 *
 * @author tulio
 */
class Response {

    /**
     *
     * @var array
     */
    private $messages;
    
    /**
     *
     * @var array
     */
    private $dataset = array();

    /**
     * 
     * @param \MilesBench\Message $message
     */
    public function addMessage(\MilesBench\Message $message) {
        $this->messages[] = $message;
    }

    /**
     * 
     * @return array
     */
    public function getMessages() {
        return $this->messages;
    }

    /**
     * 
     * @param array $messages
     */
    public function setMessages(array $messages) {
        $this->messages = $messages;
    }

    /**
     * 
     * @return array
     */
    public function getDataset() {
        return $this->dataset;
    }

    /**
     * 
     * @param array $dataset
     */
    public function setDataset($dataset) {
        $this->dataset = $dataset;
    }

    /**
     * 
     * @return string
     */
    public function __toString() {
        $data = array();
        $messages = $this->getMessages();
        if (isset($messages)) {            
            foreach ($messages as $message) {
                $data['message']['text'] = $message->getText();
                $data['message']['type'] = $message->getType();
            }
        }

        $data['dataset'] = $this->getDataset();
        return json_encode($data);
    }

}