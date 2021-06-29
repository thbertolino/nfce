<?php

Class appModel {

    public $db;
    // public $dbPDV;
    public $post;

    public function __construct() {
        $this->initApp();
    }
    
    public function initApp() {
        $registry = Registry::getInstance();
        if ($registry->get('db') == false) {
            $registry->set('db', new DB);
        }
        $this->db = $registry->get('db');
        // Banco do PDV
        // if ($registry->get('dbPDV') == false) {
        //     $registry->set('dbPDV', new DB('databasePDV'));
        // }
        // $this->dbPDV = $registry->get('dbPDV');
    }
}
