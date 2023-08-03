<?php
namespace MilesBench\Request;

/**
 * Description of Request
 *
 * @author tulio
 */
class Request {

    private $row = array();

    public function getRow() {
        return $this->row;
    }

    public function setRow($row) {
        $this->row = $row;
    }

}