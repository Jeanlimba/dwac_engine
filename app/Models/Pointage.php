<?php
use Core\Database;

/**
 * Modèle des pointages (présence biométrique).
 *
 * Toutes les requêtes sont filtrées par tenant_id : un gestionnaire ne voit
 * que la présence des employés de son entreprise (isolation multi-tenant).
 */
class Pointage {
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    /** Pointages du jour pour un tenant, joints aux employés. */
    public function getTodayByTenant($tenantId) {
        $this->db->query("SELECT p.*, e.nom, e.prenom, e.matricule
                          FROM pointages p
                          JOIN employees e ON p.employe_id = e.id
                          WHERE p.tenant_id = :tenant_id
                            AND DATE(p.date_heure) = CURDATE()
                          ORDER BY p.date_heure DESC");
        $this->db->bind(':tenant_id', $tenantId);
        return $this->db->resultSet();
    }

    /**
     * Synthèse journalière (1ère arrivée / dernière sortie / durée) par
     * employé et par jour, filtrée par tenant et critères optionnels.
     */
    public function getDailySummary($tenantId, $employeeId = null, $startDate = null, $endDate = null) {
        $sql = "SELECT e.id AS employe_id, e.nom, e.prenom, e.matricule,
                       DATE(p.date_heure) AS date_jour,
                       MIN(p.date_heure) AS heure_arrivee,
                       MAX(p.date_heure) AS heure_sortie,
                       TIMEDIFF(MAX(p.date_heure), MIN(p.date_heure)) AS duree_presence
                FROM pointages p
                JOIN employees e ON p.employe_id = e.id
                WHERE p.tenant_id = :tenant_id";
        if ($employeeId) { $sql .= " AND p.employe_id = :employee_id"; }
        if ($startDate)  { $sql .= " AND DATE(p.date_heure) >= :start_date"; }
        if ($endDate)    { $sql .= " AND DATE(p.date_heure) <= :end_date"; }
        $sql .= " GROUP BY e.id, DATE(p.date_heure)
                  ORDER BY DATE(p.date_heure) DESC, e.nom ASC";

        $this->db->query($sql);
        $this->db->bind(':tenant_id', $tenantId);
        if ($employeeId) { $this->db->bind(':employee_id', $employeeId); }
        if ($startDate)  { $this->db->bind(':start_date', $startDate); }
        if ($endDate)    { $this->db->bind(':end_date', $endDate); }
        return $this->db->resultSet();
    }

    /** Détail des passages d'un employé pour un jour donné (tenant-scopé). */
    public function getDayDetail($tenantId, $employeeId, $date) {
        $this->db->query("SELECT p.*
                          FROM pointages p
                          WHERE p.tenant_id = :tenant_id
                            AND p.employe_id = :employee_id
                            AND DATE(p.date_heure) = :date
                          ORDER BY p.date_heure ASC");
        $this->db->bind(':tenant_id', $tenantId);
        $this->db->bind(':employee_id', $employeeId);
        $this->db->bind(':date', $date);
        return $this->db->resultSet();
    }
}
