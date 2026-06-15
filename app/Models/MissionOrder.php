<?php
use Core\Database;

class MissionOrder {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function create($data) {
        $this->db->query("INSERT INTO mission_orders (mission_id, tenant_id, order_number, type, employee_id, object, itinerary, means_of_transport, departure_date, return_date, status, signatory_name, signatory_role, sign_city, footer_text, agency_name, agency_address, agency_phone) 
                         VALUES (:mission_id, :tenant_id, :order_number, :type, :employee_id, :object, :itinerary, :means_of_transport, :departure_date, :return_date, :status, :signatory_name, :signatory_role, :sign_city, :footer_text, :agency_name, :agency_address, :agency_phone)");
        
        $this->db->bind(':mission_id', $data['mission_id']);
        $this->db->bind(':tenant_id', $data['tenant_id']);
        $this->db->bind(':order_number', $data['order_number']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':object', $data['object']);
        $this->db->bind(':itinerary', $data['itinerary']);
        $this->db->bind(':means_of_transport', $data['means_of_transport']);
        $this->db->bind(':departure_date', $data['departure_date']);
        $this->db->bind(':return_date', $data['return_date']);
        $this->db->bind(':status', $data['status'] ?? 'Brouillon');
        $this->db->bind(':signatory_name', $data['signatory_name'] ?? 'NGUBI Mac');
        $this->db->bind(':signatory_role', $data['signatory_role'] ?? 'Managing Director');
        $this->db->bind(':sign_city', $data['sign_city'] ?? 'Kinshasa');
        $this->db->bind(':footer_text', $data['footer_text'] ?? null);
        $this->db->bind(':agency_name', $data['agency_name'] ?? null);
        $this->db->bind(':agency_address', $data['agency_address'] ?? null);
        $this->db->bind(':agency_phone', $data['agency_phone'] ?? null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function getOrdersByMission($mission_id) {
        $this->db->query("SELECT mo.*, e.nom, e.prenom, m.title as mission_title 
                         FROM mission_orders mo 
                         LEFT JOIN missions m ON mo.mission_id = m.id
                         LEFT JOIN employees e ON mo.employee_id = e.id 
                         WHERE mo.mission_id = :mission_id 
                         ORDER BY mo.created_at DESC");
        $this->db->bind(':mission_id', $mission_id);
        return $this->db->resultSet();
    }

    public function getOrderById($id) {
        $this->db->query("SELECT mo.*, e.nom, e.prenom, m.title as mission_title 
                         FROM mission_orders mo 
                         LEFT JOIN missions m ON mo.mission_id = m.id
                         LEFT JOIN employees e ON mo.employee_id = e.id 
                         WHERE mo.id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function update($data) {
        $this->db->query("UPDATE mission_orders SET 
                         order_number = :order_number,
                         type = :type,
                         employee_id = :employee_id,
                         object = :object,
                         itinerary = :itinerary,
                         means_of_transport = :means_of_transport,
                         departure_date = :departure_date,
                         return_date = :return_date,
                         status = :status,
                         signatory_name = :signatory_name,
                         signatory_role = :signatory_role,
                         sign_city = :sign_city,
                         footer_text = :footer_text,
                         agency_name = :agency_name,
                         agency_address = :agency_address,
                         agency_phone = :agency_phone
                         WHERE id = :id");
        
        $this->db->bind(':order_number', $data['order_number']);
        $this->db->bind(':type', $data['type']);
        $this->db->bind(':employee_id', $data['employee_id']);
        $this->db->bind(':object', $data['object']);
        $this->db->bind(':itinerary', $data['itinerary']);
        $this->db->bind(':means_of_transport', $data['means_of_transport']);
        $this->db->bind(':departure_date', $data['departure_date']);
        $this->db->bind(':return_date', $data['return_date']);
        $this->db->bind(':status', $data['status']);
        $this->db->bind(':signatory_name', $data['signatory_name']);
        $this->db->bind(':signatory_role', $data['signatory_role']);
        $this->db->bind(':sign_city', $data['sign_city']);
        $this->db->bind(':footer_text', $data['footer_text']);
        $this->db->bind(':agency_name', $data['agency_name']);
        $this->db->bind(':agency_address', $data['agency_address']);
        $this->db->bind(':agency_phone', $data['agency_phone']);
        $this->db->bind(':id', $data['id']);

        return $this->db->execute();
    }

    public function validate($id, $user_id) {
        $this->db->query("UPDATE mission_orders SET status = 'Validé', validated_by = :user_id, validated_at = NOW() WHERE id = :id");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function reject($id, $user_id) {
        $this->db->query("UPDATE mission_orders SET status = 'Rejeté', validated_by = :user_id, validated_at = NOW() WHERE id = :id");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function delete($id) {
        $this->db->query("DELETE FROM mission_orders WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }

    public function getOrdersByTenant($tenant_id) {
        $this->db->query("SELECT mo.*, e.nom, e.prenom, m.title as mission_title 
                         FROM mission_orders mo 
                         LEFT JOIN missions m ON mo.mission_id = m.id
                         LEFT JOIN employees e ON mo.employee_id = e.id 
                         WHERE mo.tenant_id = :tenant_id 
                         ORDER BY mo.created_at DESC");
        $this->db->bind(':tenant_id', $tenant_id);
        return $this->db->resultSet();
    }
}
