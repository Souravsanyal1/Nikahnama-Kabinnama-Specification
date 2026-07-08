<?php
// app/models/Nikahnama.php

require_once __DIR__ . '/../config/database.php';

class Nikahnama {
    private $db;

    public function __construct() {
        $this->db = Database::connect();
    }

    /**
     * Generate unique Certificate Number
     */
    public function generateCertificateNo() {
        $prefix = 'NIK-' . date('Ymd') . '-';
        
        // Find how many certificates are created today
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM nikahnama WHERE DATE(created_at) = CURRENT_DATE()");
        $stmt->execute();
        $row = $stmt->fetch();
        $next_num = str_pad($row['count'] + 1, 4, '0', STR_PAD_LEFT);
        
        $cert_no = $prefix . $next_num;
        
        // Double check uniqueness
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM nikahnama WHERE certificate_no = ?");
        $stmt->execute([$cert_no]);
        $check = $stmt->fetch();
        if ($check['count'] > 0) {
            // If exists, append unique time suffix
            $cert_no = $prefix . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        return $cert_no;
    }

    /**
     * Generate unique Registration Number
     */
    public function generateRegistrationNo() {
        $prefix = 'REG-' . date('Ymd') . '-';
        
        // Find count
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM nikahnama WHERE DATE(created_at) = CURRENT_DATE()");
        $stmt->execute();
        $row = $stmt->fetch();
        $next_num = str_pad($row['count'] + 1, 4, '0', STR_PAD_LEFT);
        
        $reg_no = $prefix . $next_num;
        
        $stmt = $this->db->prepare("SELECT COUNT(*) as count FROM nikahnama WHERE registration_no = ?");
        $stmt->execute([$reg_no]);
        $check = $stmt->fetch();
        if ($check['count'] > 0) {
            $reg_no = $prefix . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        return $reg_no;
    }

    /**
     * Insert a new Nikahnama record
     */
    public function create($data) {
        $sql = "INSERT INTO nikahnama (
            certificate_no, registration_no, marriage_date, marriage_time, marriage_place,
            mahr_amount, currency, mahr_status,
            bride_name, bride_father, bride_mother, bride_birth, bride_nid, bride_passport, bride_phone, bride_address,
            groom_name, groom_father, groom_mother, groom_birth, groom_nid, groom_passport, groom_phone, groom_address,
            wali_name, registrar_name, registrar_license, registrar_phone, registrar_address,
            witness1_name, witness1_nid, witness2_name, witness2_nid, notes, qr_code
        ) VALUES (
            :certificate_no, :registration_no, :marriage_date, :marriage_time, :marriage_place,
            :mahr_amount, :currency, :mahr_status,
            :bride_name, :bride_father, :bride_mother, :bride_birth, :bride_nid, :bride_passport, :bride_phone, :bride_address,
            :groom_name, :groom_father, :groom_mother, :groom_birth, :groom_nid, :groom_passport, :groom_phone, :groom_address,
            :wali_name, :registrar_name, :registrar_license, :registrar_phone, :registrar_address,
            :witness1_name, :witness1_nid, :witness2_name, :witness2_nid, :notes, :qr_code
        )";

        $stmt = $this->db->prepare($sql);
        
        // Execute with mapped parameters
        $result = $stmt->execute([
            ':certificate_no' => $data['certificate_no'],
            ':registration_no' => $data['registration_no'],
            ':marriage_date' => $data['marriage_date'],
            ':marriage_time' => $data['marriage_time'],
            ':marriage_place' => $data['marriage_place'],
            ':mahr_amount' => $data['mahr_amount'],
            ':currency' => $data['currency'] ?? 'BDT',
            ':mahr_status' => $data['mahr_status'],
            ':bride_name' => $data['bride_name'],
            ':bride_father' => $data['bride_father'],
            ':bride_mother' => $data['bride_mother'],
            ':bride_birth' => $data['bride_birth'],
            ':bride_nid' => !empty($data['bride_nid']) ? $data['bride_nid'] : null,
            ':bride_passport' => !empty($data['bride_passport']) ? $data['bride_passport'] : null,
            ':bride_phone' => $data['bride_phone'],
            ':bride_address' => $data['bride_address'],
            ':groom_name' => $data['groom_name'],
            ':groom_father' => $data['groom_father'],
            ':groom_mother' => $data['groom_mother'],
            ':groom_birth' => $data['groom_birth'],
            ':groom_nid' => !empty($data['groom_nid']) ? $data['groom_nid'] : null,
            ':groom_passport' => !empty($data['groom_passport']) ? $data['groom_passport'] : null,
            ':groom_phone' => $data['groom_phone'],
            ':groom_address' => $data['groom_address'],
            ':wali_name' => !empty($data['wali_name']) ? $data['wali_name'] : null,
            ':registrar_name' => $data['registrar_name'],
            ':registrar_license' => $data['registrar_license'],
            ':registrar_phone' => $data['registrar_phone'],
            ':registrar_address' => $data['registrar_address'],
            ':witness1_name' => $data['witness1_name'],
            ':witness1_nid' => $data['witness1_nid'],
            ':witness2_name' => $data['witness2_name'],
            ':witness2_nid' => $data['witness2_nid'],
            ':notes' => !empty($data['notes']) ? $data['notes'] : null,
            ':qr_code' => $data['qr_code'] ?? null
        ]);

        if ($result) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Update an existing Nikahnama record
     */
    public function update($id, $data) {
        $sql = "UPDATE nikahnama SET 
            marriage_date = :marriage_date,
            marriage_time = :marriage_time,
            marriage_place = :marriage_place,
            mahr_amount = :mahr_amount,
            currency = :currency,
            mahr_status = :mahr_status,
            bride_name = :bride_name,
            bride_father = :bride_father,
            bride_mother = :bride_mother,
            bride_birth = :bride_birth,
            bride_nid = :bride_nid,
            bride_passport = :bride_passport,
            bride_phone = :bride_phone,
            bride_address = :bride_address,
            groom_name = :groom_name,
            groom_father = :groom_father,
            groom_mother = :groom_mother,
            groom_birth = :groom_birth,
            groom_nid = :groom_nid,
            groom_passport = :groom_passport,
            groom_phone = :groom_phone,
            groom_address = :groom_address,
            wali_name = :wali_name,
            registrar_name = :registrar_name,
            registrar_license = :registrar_license,
            registrar_phone = :registrar_phone,
            registrar_address = :registrar_address,
            witness1_name = :witness1_name,
            witness1_nid = :witness1_nid,
            witness2_name = :witness2_name,
            witness2_nid = :witness2_nid,
            notes = :notes
            WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $id,
            ':marriage_date' => $data['marriage_date'],
            ':marriage_time' => $data['marriage_time'],
            ':marriage_place' => $data['marriage_place'],
            ':mahr_amount' => $data['mahr_amount'],
            ':currency' => $data['currency'] ?? 'BDT',
            ':mahr_status' => $data['mahr_status'],
            ':bride_name' => $data['bride_name'],
            ':bride_father' => $data['bride_father'],
            ':bride_mother' => $data['bride_mother'],
            ':bride_birth' => $data['bride_birth'],
            ':bride_nid' => !empty($data['bride_nid']) ? $data['bride_nid'] : null,
            ':bride_passport' => !empty($data['bride_passport']) ? $data['bride_passport'] : null,
            ':bride_phone' => $data['bride_phone'],
            ':bride_address' => $data['bride_address'],
            ':groom_name' => $data['groom_name'],
            ':groom_father' => $data['groom_father'],
            ':groom_mother' => $data['groom_mother'],
            ':groom_birth' => $data['groom_birth'],
            ':groom_nid' => !empty($data['groom_nid']) ? $data['groom_nid'] : null,
            ':groom_passport' => !empty($data['groom_passport']) ? $data['groom_passport'] : null,
            ':groom_phone' => $data['groom_phone'],
            ':groom_address' => $data['groom_address'],
            ':wali_name' => !empty($data['wali_name']) ? $data['wali_name'] : null,
            ':registrar_name' => $data['registrar_name'],
            ':registrar_license' => $data['registrar_license'],
            ':registrar_phone' => $data['registrar_phone'],
            ':registrar_address' => $data['registrar_address'],
            ':witness1_name' => $data['witness1_name'],
            ':witness1_nid' => $data['witness1_nid'],
            ':witness2_name' => $data['witness2_name'],
            ':witness2_nid' => $data['witness2_nid'],
            ':notes' => !empty($data['notes']) ? $data['notes'] : null
        ]);
    }

    /**
     * Delete a Nikahnama record
     */
    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM nikahnama WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Get record by database ID
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM nikahnama WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Get record by Certificate Number (for verification/search)
     */
    public function getByCertificateNo($certNo) {
        $stmt = $this->db->prepare("SELECT * FROM nikahnama WHERE certificate_no = ?");
        $stmt->execute([$certNo]);
        return $stmt->fetch();
    }

    /**
     * Search certificates with filters
     */
    public function search($query_str) {
        if (empty($query_str)) {
            $stmt = $this->db->prepare("SELECT * FROM nikahnama ORDER BY id DESC LIMIT 50");
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $term = '%' . $query_str . '%';
        $sql = "SELECT * FROM nikahnama WHERE 
            certificate_no LIKE :term OR 
            registration_no LIKE :term OR 
            bride_name LIKE :term OR 
            groom_name LIKE :term OR 
            bride_nid LIKE :term OR 
            groom_nid LIKE :term OR 
            bride_phone LIKE :term OR 
            groom_phone LIKE :term OR 
            marriage_date LIKE :term
            ORDER BY id DESC";
            
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':term' => $term]);
        return $stmt->fetchAll();
    }

    /**
     * Count Total Certificates
     */
    public function countAll() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM nikahnama");
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }

    /**
     * Count Certificates registered today
     */
    public function countToday() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM nikahnama WHERE DATE(created_at) = CURRENT_DATE()");
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }

    /**
     * Count Certificates registered this month
     */
    public function countThisMonth() {
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM nikahnama WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE())");
        $stmt->execute();
        $row = $stmt->fetch();
        return $row['total'] ?? 0;
    }

    /**
     * Get Recent Certificates
     */
    public function getRecent($limit = 5) {
        $stmt = $this->db->prepare("SELECT * FROM nikahnama ORDER BY id DESC LIMIT " . intval($limit));
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Update QR Code reference
     */
    public function updateQrCode($id, $qr_code) {
        $stmt = $this->db->prepare("UPDATE nikahnama SET qr_code = ? WHERE id = ?");
        return $stmt->execute([$qr_code, $id]);
    }
}
