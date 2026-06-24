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

    /**
     * Ingestion par lot des pointages envoyés par le collecteur local.
     * Chaque enregistrement attendu : ['zk_id' => int, 'date_heure' => 'Y-m-d H:i:s', 'type' => int].
     * La résolution zk_id -> employé est SCOPÉE au tenant (un collecteur ne peut
     * écrire que pour son entreprise). La déduplication repose sur la contrainte
     * UNIQUE(employe_id, date_heure) via INSERT IGNORE.
     *
     * @return array{inserted:int,skipped:int}
     */
    public function ingestBatch($tenantId, array $records) {
        $inserted = 0;
        $skipped = 0;

        foreach ($records as $r) {
            $zkId = $r['zk_id'] ?? null;
            $dateHeure = $r['date_heure'] ?? null;
            $type = (int) ($r['type'] ?? 0);

            if (!$zkId || !$dateHeure) {
                $skipped++;
                continue;
            }

            // Employé correspondant, DANS le tenant ciblé uniquement.
            $this->db->query("SELECT id FROM employees WHERE zk_id = :zk AND tenant_id = :tenant_id");
            $this->db->bind(':zk', $zkId);
            $this->db->bind(':tenant_id', $tenantId);
            $employee = $this->db->single();

            if (!$employee) {
                $skipped++;
                continue;
            }

            $this->db->query("INSERT IGNORE INTO pointages (employe_id, tenant_id, date_heure, type_pointage)
                              VALUES (:employe_id, :tenant_id, :date_heure, :type_pointage)");
            $this->db->bind(':employe_id', $employee->id);
            $this->db->bind(':tenant_id', $tenantId);
            $this->db->bind(':date_heure', $dateHeure);
            $this->db->bind(':type_pointage', $type);
            $this->db->execute();

            if ($this->db->rowCount() > 0) {
                $inserted++;
            } else {
                $skipped++; // doublon ignoré
            }
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
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
