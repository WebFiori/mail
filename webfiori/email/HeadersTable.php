<?php


namespace webfiori\email;

use webfiori\ui\HTMLTable;

/**
 * This table is used to show headers values for specific email. 
 *
 * @author Ibrahim
 */
class HeadersTable extends HTMLTable {
    public function __construct() {
        parent::__construct(1, 2);
        $this->setID('email-headers-table');
        $this->getCell(0, 0)->setStyle([
            'font-weight' => 'bold'
        ]);
        $this->getCell(0, 1)->setStyle([
            'font-weight' => 'bold'
        ]);
        $this->setValue(0, 0, 'Header Name');
        $this->setValue(0, 1, 'Header Value');
    }
    public function addHeader(string $name, string $value) {
        $this->addRow([]);
        $this->getCell($this->childrenCount() - 1, 1)->text($value, true);
        $this->getCell($this->childrenCount() - 1, 0)->setStyle([
            'font-weight' => 'bold'
        ])->text($name, true);
    }
}
