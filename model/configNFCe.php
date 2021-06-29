<?php

class configNFCe extends appModel
{
    public function get_all() {
        $db = new DB('databasePDV');
        $db->query = "SELECT * FROM tec_settings";
        $data = $db->fetch();
        return (isset($data[0])) ? $data : null;
    }

    public function get_name() {
        $db = new DB('databasePDV');
        $db->query = "SELECT * FROM tec_customers";
        $data = $db->fetch();
        return (isset($data[0])) ? $data : null;
    }

    public function get_product() {
        $db = new DB('databasePDV');
        $db->query = "SELECT * FROM tec_products";
        $data = $db->fetch();
        return (isset($data[0])) ? $data : null;
    }

    public function get_sales() {
        $db = new DB('databasePDV');
        $db->query = "SELECT * FROM tec_sales";
        $data = $db->fetch();
        return (isset($data[0])) ? $data : null;
    }

    public function get_sales_by_id($id)
    {
        $db = new DB('databasePDV');
        $this->db->query = "SELECT * FROM tec_sales WHERE id  = $id;";
        $data = $this->db->fetch();
        return (isset($data[0])) ? $data[0] : null;
    }

    public function get_nfce() {
        $db = new DB('databasePDV');
        $db->query = "SELECT * FROM tec_nfce";
        $data = $db->fetch();
        return (isset($data[0])) ? $data : null;
    }

    // public function get_by_id($id)
    // {
    //     $db = new DB('databasePDV');
    //     $this->db->query = "SELECT tec_sales.*, tec_customers.id, tec_customers.name FROM tec_sales "
    //         . "INNER JOIN tec_customers ON (tec_customers.id = tec_sales.customer_id ) "
    //         . "WHERE id = $id;";
    //     $data = $this->db->fetch();
    //     return (isset($data[0])) ? $data[0] : null;
    // }


    // protected $table = 'tec_settings';
    // // protected $primaryKey = 'id_config';
    // protected $allowedFields = [
    //     'logradouro',
    //     'complemento'
    // ];
    // protected $useTimestamps = true;
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';
    // protected $deletedField  = 'deleted_at';

}
