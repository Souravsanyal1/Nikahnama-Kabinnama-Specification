<?php
// app/models/Nikahnama.php

class Nikahnama {
    private $projectId = 'nikahnama-181b3';
    private $baseUrl;

    public function __construct() {
        $this->baseUrl = "https://firestore.googleapis.com/v1/projects/" . $this->projectId . "/databases/(default)/documents";
        
        // Auto-seed default users if they are not in the cloud yet
        $this->seedUsersIfEmpty();
    }

    /**
     * cURL REST Client Helper
     */
    private function request($path, $method = 'GET', $data = null) {
        $url = $this->baseUrl . $path;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Prevent SSL issues in local environments
        
        if ($data !== null) {
            $json = json_encode($data);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json)
            ]);
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode >= 400) {
            return null;
        }
        
        return json_decode($response, true);
    }

    /**
     * Map Firestore Document format to standard PHP array
     */
    public function flattenDocument($doc) {
        if (!$doc || !isset($doc['fields'])) {
            return null;
        }
        
        $fields = $doc['fields'];
        $data = [];
        
        // Parse document ID from Firestore resource name path
        $parts = explode('/', $doc['name']);
        $data['id'] = end($parts);
        
        foreach ($fields as $key => $val) {
            if (isset($val['stringValue'])) {
                $data[$key] = $val['stringValue'];
            } elseif (isset($val['integerValue'])) {
                $data[$key] = intval($val['integerValue']);
            } elseif (isset($val['doubleValue'])) {
                $data[$key] = floatval($val['doubleValue']);
            } elseif (isset($val['booleanValue'])) {
                $data[$key] = (bool)$val['booleanValue'];
            } else {
                $data[$key] = null;
            }
        }
        
        $data['created_at'] = isset($doc['createTime']) ? date('Y-m-d H:i:s', strtotime($doc['createTime'])) : date('Y-m-d H:i:s');
        $data['updated_at'] = isset($doc['updateTime']) ? date('Y-m-d H:i:s', strtotime($doc['updateTime'])) : date('Y-m-d H:i:s');
        
        return $data;
    }

    /**
     * Map standard PHP array to Firestore request payload
     */
    private function toFirestorePayload($data) {
        $fields = [];
        foreach ($data as $key => $value) {
            if ($value === null) {
                $fields[$key] = ['nullValue' => null];
            } elseif (is_bool($value)) {
                $fields[$key] = ['booleanValue' => $value];
            } elseif (is_int($value)) {
                $fields[$key] = ['integerValue' => strval($value)];
            } elseif (is_float($value) || is_numeric($value)) {
                $fields[$key] = ['doubleValue' => floatval($value)];
            } else {
                $fields[$key] = ['stringValue' => strval($value)];
            }
        }
        return ['fields' => $fields];
    }

    /**
     * Auto-seed default users in cloud Firestore if empty
     */
    private function seedUsersIfEmpty() {
        $res = $this->request('/users');
        if (!$res || !isset($res['documents']) || empty($res['documents'])) {
            $users = [
                [
                    'username' => 'admin',
                    'password' => '$2y$10$IriuGDloH/z1dmimyy0xV.sBsM/7I7JMI1d2ZlOI.7/1fsjo11aJW',
                    'fullname' => 'Administrator',
                    'role' => 'admin'
                ],
                [
                    'username' => 'sourav.sanya.dev@gmail.com',
                    'password' => '$2y$10$jHUm2r89uS8tc.EL.ICeKOKGAHNxevQzkfmkBoPCZ1wsYtQg/v4py',
                    'fullname' => 'Sourav Dev',
                    'role' => 'admin'
                ]
            ];
            
            foreach ($users as $user) {
                $payload = $this->toFirestorePayload($user);
                $this->request('/users', 'POST', $payload);
            }
        }
    }

    /**
     * Fetch user record by username
     */
    public function getUserByUsername($username) {
        $res = $this->request('/users?pageSize=100');
        if ($res && isset($res['documents'])) {
            foreach ($res['documents'] as $doc) {
                $user = $this->flattenDocument($doc);
                if (strcasecmp($user['username'], $username) === 0) {
                    return $user;
                }
            }
        }
        return null;
    }

    /**
     * Generate unique Certificate Number
     */
    public function generateCertificateNo() {
        $prefix = 'NIK-' . date('Ymd') . '-';
        $today = date('Y-m-d');
        
        $docs = $this->getAll();
        $count = 0;
        foreach ($docs as $doc) {
            if (isset($doc['created_at']) && substr($doc['created_at'], 0, 10) === $today) {
                $count++;
            }
        }
        
        $next_num = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $cert_no = $prefix . $next_num;
        
        if ($this->getByCertificateNo($cert_no) !== null) {
            $cert_no = $prefix . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        return $cert_no;
    }

    /**
     * Generate unique Registration Number
     */
    public function generateRegistrationNo() {
        $prefix = 'REG-' . date('Ymd') . '-';
        $today = date('Y-m-d');
        
        $docs = $this->getAll();
        $count = 0;
        foreach ($docs as $doc) {
            if (isset($doc['created_at']) && substr($doc['created_at'], 0, 10) === $today) {
                $count++;
            }
        }
        
        $next_num = str_pad($count + 1, 4, '0', STR_PAD_LEFT);
        $reg_no = $prefix . $next_num;
        
        // Verify uniqueness
        $exists = false;
        foreach ($docs as $doc) {
            if (strcasecmp($doc['registration_no'], $reg_no) === 0) {
                $exists = true;
                break;
            }
        }
        if ($exists) {
            $reg_no = $prefix . str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        }
        
        return $reg_no;
    }

    /**
     * Insert a new Nikahnama record
     */
    public function create($data) {
        $payload = $this->toFirestorePayload($data);
        $res = $this->request('/nikahnama', 'POST', $payload);
        if ($res && isset($res['name'])) {
            $flat = $this->flattenDocument($res);
            return $flat['id'] ?? true;
        }
        return false;
    }

    /**
     * Update an existing Nikahnama record
     */
    public function update($id, $data) {
        $payload = $this->toFirestorePayload($data);
        
        // Build patch query parameters to instruct Firestore which fields to write
        $queryParams = [];
        foreach ($data as $key => $val) {
            $queryParams[] = 'updateMask.fieldPaths=' . urlencode($key);
        }
        $queryString = '?' . implode('&', $queryParams);
        
        $res = $this->request('/nikahnama/' . $id . $queryString, 'PATCH', $payload);
        return $res !== null;
    }

    /**
     * Delete a Nikahnama record
     */
    public function delete($id) {
        $res = $this->request('/nikahnama/' . $id, 'DELETE');
        return $res !== null;
    }

    /**
     * Get record by database ID
     */
    public function getById($id) {
        $res = $this->request('/nikahnama/' . $id);
        if ($res) {
            return $this->flattenDocument($res);
        }
        return null;
    }

    /**
     * Get record by Certificate Number (for verification/search)
     */
    public function getByCertificateNo($certNo) {
        $docs = $this->getAll();
        foreach ($docs as $doc) {
            if (strcasecmp($doc['certificate_no'], $certNo) === 0) {
                return $doc;
            }
        }
        return null;
    }

    /**
     * Get all certificates from collection
     */
    public function getAll() {
        $res = $this->request('/nikahnama?pageSize=300'); // Higher page limit to return all
        $documents = [];
        if ($res && isset($res['documents'])) {
            foreach ($res['documents'] as $doc) {
                $flat = $this->flattenDocument($doc);
                if ($flat) {
                    $documents[] = $flat;
                }
            }
        }
        return $documents;
    }

    /**
     * Search certificates with filters
     */
    public function search($query_str) {
        $docs = $this->getAll();
        if (empty($query_str)) {
            usort($docs, function($a, $b) {
                return strcmp($b['created_at'], $a['created_at']);
            });
            return array_slice($docs, 0, 50);
        }

        $results = [];
        $query_str = strtolower(trim($query_str));
        
        foreach ($docs as $doc) {
            $match = false;
            foreach (['certificate_no', 'registration_no', 'bride_name', 'groom_name', 'bride_nid', 'groom_nid', 'bride_phone', 'groom_phone', 'marriage_date'] as $field) {
                if (isset($doc[$field]) && stripos(strtolower($doc[$field]), $query_str) !== false) {
                    $match = true;
                    break;
                }
            }
            if ($match) {
                $results[] = $doc;
            }
        }
        
        usort($results, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });
        
        return $results;
    }

    /**
     * Count Total Certificates
     */
    public function countAll() {
        return count($this->getAll());
    }

    /**
     * Count Certificates registered today
     */
    public function countToday() {
        $docs = $this->getAll();
        $count = 0;
        $today = date('Y-m-d');
        foreach ($docs as $doc) {
            if (isset($doc['created_at']) && substr($doc['created_at'], 0, 10) === $today) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Count Certificates registered this month
     */
    public function countThisMonth() {
        $docs = $this->getAll();
        $count = 0;
        $month = date('Y-m');
        foreach ($docs as $doc) {
            if (isset($doc['created_at']) && substr($doc['created_at'], 0, 7) === $month) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Get Recent Certificates
     */
    public function getRecent($limit = 5) {
        $docs = $this->getAll();
        usort($docs, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });
        return array_slice($docs, 0, $limit);
    }
}
