<?php
/**
 * User Model Class
 * Handles customer user data operations
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getConnection();
    }
    
    /**
     * Check if database connection is available
     * 
     * @return bool
     */
    public function isConnected() {
        return $this->db !== null;
    }
    
    /**
     * Get user by ID
     * 
     * @param int $userId
     * @return array|false
     */
    public function findById($userId) {
        if (!$this->isConnected()) return false;
        
        $stmt = $this->db->prepare("SELECT * FROM users WHERE user_id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
        
        if ($user) {
            unset($user['password_hash'], $user['reset_token'], $user['verification_token']);
        }
        
        return $user;
    }
    
    /**
     * Get user by email
     * 
     * @param string $email
     * @return array|false
     */
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }
    
    /**
     * Create new user
     * 
     * @param array $data User data
     * @return int|false User ID or false
     */
    public function create($data) {
        $sql = "INSERT INTO users (
            email, password_hash, first_name, last_name, phone, 
            address, city, state, zip_code, country, 
            date_of_birth, gender, verification_token, preferred_language
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['email'],
            $data['password_hash'],
            $data['first_name'],
            $data['last_name'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['city'] ?? null,
            $data['state'] ?? null,
            $data['zip_code'] ?? null,
            $data['country'] ?? 'Morocco',
            $data['date_of_birth'] ?? null,
            $data['gender'] ?? null,
            $data['verification_token'] ?? null,
            $data['preferred_language'] ?? 'en'
        ]);
        
        return $result ? $this->db->lastInsertId() : false;
    }
    
    /**
     * Update user
     * 
     * @param int $userId
     * @param array $data User data to update
     * @return bool
     */
    public function update($userId, $data) {
        $allowedFields = [
            'first_name', 'last_name', 'phone', 'profile_picture',
            'address', 'city', 'state', 'zip_code', 'country',
            'date_of_birth', 'gender', 'preferred_language', 'timezone'
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
        
        $values[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE user_id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
    
    /**
     * Update password
     * 
     * @param int $userId
     * @param string $passwordHash
     * @return bool
     */
    public function updatePassword($userId, $passwordHash) {
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
        return $stmt->execute([$passwordHash, $userId]);
    }
    
    /**
     * Update last login
     * 
     * @param int $userId
     * @return bool
     */
    public function updateLastLogin($userId) {
        $stmt = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE user_id = ?");
        return $stmt->execute([$userId]);
    }
    
    /**
     * Verify email
     * 
     * @param string $token
     * @return bool
     */
    public function verifyEmail($token) {
        $stmt = $this->db->prepare(
            "UPDATE users SET email_verified = TRUE, verification_token = NULL 
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
            "UPDATE users SET reset_token = ?, reset_token_expires = ? 
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
            "UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL 
             WHERE reset_token = ? AND reset_token_expires > NOW()"
        );
        return $stmt->execute([$passwordHash, $token]);
    }
    
    /**
     * Update account status
     * 
     * @param int $userId
     * @param string $status
     * @return bool
     */
    public function updateStatus($userId, $status) {
        $stmt = $this->db->prepare("UPDATE users SET account_status = ? WHERE user_id = ?");
        return $stmt->execute([$status, $userId]);
    }
    
    /**
     * Get user bookings count
     * 
     * @param int $userId
     * @return int
     */
    public function getBookingsCount($userId) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM bookings WHERE user_id = ?");
        $stmt->execute([$userId]);
        return $stmt->fetchColumn();
    }
    
    /**
     * Get user upcoming bookings
     * 
     * @param int $userId
     * @param int $limit
     * @return array
     */
    public function getUpcomingBookings($userId, $limit = 5) {
        if (!$this->isConnected()) return [];
        
        try {
            $stmt = $this->db->prepare(
                "SELECT b.*, s.service_name, p.first_name as provider_first_name, 
                        p.last_name as provider_last_name, p.profile_picture as provider_picture
                 FROM bookings b
                 JOIN services s ON b.service_id = s.service_id
                 JOIN providers p ON b.provider_id = p.provider_id
                 WHERE b.user_id = ? AND b.booking_status IN ('pending', 'confirmed')
                 ORDER BY b.booking_date ASC, b.booking_time ASC
                 LIMIT ?"
            );
            $stmt->execute([$userId, $limit]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("getUpcomingBookings error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Delete user (soft delete by updating status)
     * 
     * @param int $userId
     * @return bool
     */
    public function delete($userId) {
        return $this->updateStatus($userId, 'deleted');
    }
}
