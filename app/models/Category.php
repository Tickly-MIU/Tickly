<?php
class Category {
    protected $pdo;
    public function __construct() {
        $this->pdo = Database::getInstance();
    }
}
