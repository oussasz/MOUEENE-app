<?php
/**
 * Provider Model Class
 * Handles service provider data operations
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

class Provider {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    /**
     * Get provider by ID
     * 
     * @param int $providerId
     * @return array|false
     */
    public function findById($providerId) {
        $stmt = $this->db->prepare("SELECT * FROM providers WHERE provider_id = ?");
        $stmt->execute([$providerId]);
        $provider = $stmt->fetch();
        
        if ($provider) {
            unset($provider['password_hash'], $provider['reset_token'], $provider['verification_token']);
        }
        
        return $provider;
    }
    
    /**
     * Get provider by email
     * 
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM providers WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Create new provider
     * 
     * @param array $data Provider data
     * @return int|false Provider ID or false
     */
    public function create($data) {
        $sql = "INSERT INTO providers (
            email, password_hash, business_name, first_name, last_name, phone, 
            address, city, state, zip_code, country, bio,
            date_of_birth, gender, verification_token, preferred_language,
            experience_years, specialization, languages_spoken
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['email'],
            $data['password_hash'],
            $data['business_name'] ?? null,
            $data['first_name'],
            $data['last_name'],
            $data['phone'],
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['zip_code'] ?? null,
            $data['country'] ?? 'Morocco',
            $data['bio'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['verification_token'] ?? null,
            $data['preferred_language'] ?? 'en',
            $data['experience_years'] ?? 0,
            $data['specialization'] ?? null,
            $data['languages_spoken'] ?? null
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update provider
     * 
     * @param int $providerId
     * @param array $data Provider data to update
     * @return bool
     */
    public function update($providerId, $data) {
        $allowedFields = [
            'business_name', 'first_name', 'last_name', 'phone', 'profile_picture',
            'bio', 'address', 'city', 'state', 'zip_code', 'country',
            'date_of_birth', 'gender', 'preferred_language', 'experience_years',
            'certification', 'specialization', 'languages_spoken', 'service_radius',
            'hourly_rate', 'availability_status'
        ];
        
        $fields = [];
        $values = [];
        
        foreach ($data as $key => $value) {
            if (in_array($key, $allowedFields)) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $values[] = $providerId;
        $sql = "UPDATE providers SET " . implode(', ', $fields) . " WHERE provider_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Update password
     * 
     * @param int $providerId
     * @param string $passwordHash
     * @return bool
     */
    public function updatePassword($providerId, $passwordHash) {
        $stmt = $this->db->prepare("UPDATE providers SET password_hash = ? WHERE provider_id = ?");
        return $stmt->execute([$passwordHash, $providerId]);
    }
    
    /**
     * Update last login
     * 
     * @param int $providerId
     * @return bool
     */
    public function updateLastLogin($providerId) {
        $stmt = $this->db->prepare("UPDATE providers SET last_login = NOW() WHERE provider_id = ?");
        return $stmt->execute([$providerId]);
    }
    
    /**
     * Verify email
     * 
     * @param string $token
     * @return bool
     */
    public function verifyEmail($token) {
        $stmt = $this->db->prepare(
            "UPDATE providers SET email_verified = TRUE, verification_token = NULL 
             WHERE verification_token = ?"
        );
        return $stmt->execute([$token]);
    }
    
    /**
     * Set reset token
     * 
     * @param string $email
     * @param string $token
     * @param string $expiry
     * @return bool
     */
    public function setResetToken($email, $token, $expiry) {
        $stmt = $this->db->prepare(
            "UPDATE providers SET reset_token = ?, reset_token_expires = ? 
             WHERE email = ?"
        );
        return $stmt->execute([$token, $expiry, $email]);
    }
    
    /**
     * Reset password
     * 
     * @param string $token
     * @param string $passwordHash
     * @return bool
     */
    public function resetPassword($token, $passwordHash) {
        $stmt = $this->db->prepare(
            "UPDATE providers SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL 
             WHERE reset_token = ? AND reset_token_expires > NOW()"
        );
        return $stmt->execute([$passwordHash, $token]);
    }
    
    /**
     * Update verification status
     * 
     * @param int $providerId
     * @param string $status
     * @return bool
     */
    public function updateVerificationStatus($providerId, $status) {
        $stmt = $this->db->prepare(
            "UPDATE providers SET verification_status = ?, verification_date = NOW() 
             WHERE provider_id = ?"
        );
        return $stmt->execute([$status, $providerId]);
    }
    
    /**
     * Update account status
     * 
     * @param int $providerId
     * @param string $status
     * @return bool
     */
    public function updateStatus($providerId, $status) {
        $stmt = $this->db->prepare("UPDATE providers SET account_status = ? WHERE provider_id = ?");
        return $stmt->execute([$status, $providerId]);
    }
    
    /**
     * Get provider services
     * 
     * @param int $providerId
     * @return array
     */
    public function getServices($providerId) {
        $stmt = $this->db->prepare(
            "SELECT ps.*, s.service_name, s.description 
             FROM provider_services ps
             JOIN services s ON ps.service_id = s.service_id
             WHERE ps.provider_id = ? AND ps.status = 'active'"
        );
        $stmt->execute([$providerId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Get provider bookings
     * 
     * @param int $providerId
     * @param string $status
     * @param int $limit
     * @return array
     */
    public function getBookings($providerId, $status = null, $limit = 10) {
        if ($status) {
            $stmt = $this->db->prepare(
                "SELECT b.*, s.service_name, u.first_name as customer_first_name, 
                        u.last_name as customer_last_name
                 FROM bookings b
                 JOIN services s ON b.service_id = s.service_id
                 JOIN users u ON b.user_id = u.user_id
                 WHERE b.provider_id = ? AND b.booking_status = ?
                 ORDER BY b.booking_date DESC, b.booking_time DESC
                 LIMIT ?"
            );
            $stmt->execute([$providerId, $status, $limit]);
        } else {
            $stmt = $this->db->prepare(
                "SELECT b.*, s.service_name, u.first_name as customer_first_name, 
                        u.last_name as customer_last_name
                 FROM bookings b
                 JOIN services s ON b.service_id = s.service_id
                 JOIN users u ON b.user_id = u.user_id
                 WHERE b.provider_id = ?
                 ORDER BY b.booking_date DESC, b.booking_time DESC
                 LIMIT ?"
            );
            $stmt->execute([$providerId, $limit]);
        }
        
        return $stmt->fetchAll();
    }
    
    /**
     * Get provider statistics
     * 
     * @param int $providerId
     * @return array
     */
    public function getStatistics($providerId) {
        $provider = $this->findById($providerId);
        
        return [
            'total_bookings' => $provider['total_bookings'] ?? 0,
            'completed_bookings' => $provider['completed_bookings'] ?? 0,
            'cancelled_bookings' => $provider['cancelled_bookings'] ?? 0,
            'average_rating' => $provider['average_rating'] ?? 0,
            'total_reviews' => $provider['total_reviews'] ?? 0,
            'response_rate' => $provider['response_rate'] ?? 0,
            'acceptance_rate' => $provider['acceptance_rate'] ?? 0
        ];
    }
    
    /**
     * Delete provider (soft delete by updating status)
     * 
     * @param int $providerId
     * @return bool
     */
    public function delete($providerId) {
        return $this->updateStatus($providerId, 'deactivated');
    }
}
