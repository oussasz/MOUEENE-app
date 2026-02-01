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
            experience_years, specialization, languages_spoken, provider_type
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
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
            $data['languages_spoken'] ?? null,
            $data['provider_type'] ?? 'freelancer'
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
            'commercial_registry_number', 'nif', 'nis',
            'provider_type',
            'availability_status'
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
            "SELECT 
                ps.provider_service_id,
                ps.provider_id,
                ps.service_id,
                ps.price,
                ps.price_type,
                ps.is_active,
                ps.total_bookings,
                ps.created_at,
                ps.updated_at,
                s.service_name,
                COALESCE(ps.description, s.description) AS description,
                s.service_image,
                s.duration_minutes,
                sc.category_name
             FROM provider_services ps
             JOIN services s ON ps.service_id = s.service_id
             LEFT JOIN service_categories sc ON s.category_id = sc.category_id
             WHERE ps.provider_id = ?
             ORDER BY ps.created_at DESC"
        );
        $stmt->execute([$providerId]);
        $rows = $stmt->fetchAll();

        if (!$rows) {
            return [];
        }

        $ids = array_values(array_filter(array_map(function ($r) {
            return isset($r['provider_service_id']) ? (int)$r['provider_service_id'] : 0;
        }, $rows)));

        if (!$ids) {
            return $rows;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $imgStmt = $this->db->prepare(
            "SELECT provider_service_id, image_url, public_id, sort_order
             FROM provider_service_images
             WHERE provider_service_id IN ($placeholders)
             ORDER BY sort_order ASC, image_id ASC"
        );
        $imgStmt->execute($ids);
        $images = $imgStmt->fetchAll();

        $byPs = [];
        foreach ($images as $img) {
            $psid = (int)($img['provider_service_id'] ?? 0);
            if (!isset($byPs[$psid])) {
                $byPs[$psid] = [];
            }
            $byPs[$psid][] = [
                'url' => $img['image_url'] ?? null,
                'public_id' => $img['public_id'] ?? null,
                'sort_order' => isset($img['sort_order']) ? (int)$img['sort_order'] : 0,
            ];
        }

        foreach ($rows as &$row) {
            $psid = (int)($row['provider_service_id'] ?? 0);
            $row['images'] = $byPs[$psid] ?? [];
        }
        unset($row);

        return $rows;
    }

    /**
     * Add a service offering for a provider
     *
     * @param int $providerId
     * @param array $data
     * @return array Newly created provider service row
     */
    public function addService($providerId, $data) {
        // Ensure provider exists
        $stmt = $this->db->prepare("SELECT provider_id FROM providers WHERE provider_id = ?");
        $stmt->execute([$providerId]);
        if (!$stmt->fetch()) {
            throw new Exception('Provider not found');
        }

        $serviceId = (int)($data['service_id'] ?? 0);
        if ($serviceId <= 0) {
            throw new Exception('Invalid service');
        }

        // Ensure service exists and is active
        $stmt = $this->db->prepare("SELECT service_id FROM services WHERE service_id = ? AND is_active = TRUE");
        $stmt->execute([$serviceId]);
        if (!$stmt->fetch()) {
            throw new Exception('Service not found');
        }

        $price = $data['price'] ?? null;
        $priceType = $data['price_type'] ?? 'fixed';
        $description = $data['description'] ?? null;
        $isActive = isset($data['is_active']) ? (bool)$data['is_active'] : true;
        $images = $data['images'] ?? [];

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare(
                "INSERT INTO provider_services (provider_id, service_id, price, price_type, description, is_active)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $providerId,
                $serviceId,
                $price,
                $priceType,
                $description,
                $isActive ? 1 : 0
            ]);

            // Keep lightweight aggregate in sync
            $stmt = $this->db->prepare(
                "UPDATE services SET total_providers = total_providers + 1 WHERE service_id = ?"
            );
            $stmt->execute([$serviceId]);

            $providerServiceId = (int)$this->db->lastInsertId();

            if (is_array($images) && count($images) > 0) {
                $max = min(10, count($images));
                $imgStmt = $this->db->prepare(
                    "INSERT INTO provider_service_images (provider_service_id, image_url, public_id, sort_order)
                     VALUES (?, ?, ?, ?)"
                );

                for ($i = 0; $i < $max; $i++) {
                    $img = $images[$i];
                    if (!is_array($img)) continue;
                    $url = trim((string)($img['url'] ?? ''));
                    $publicId = isset($img['public_id']) ? trim((string)$img['public_id']) : null;
                    if ($url === '') continue;
                    $imgStmt->execute([$providerServiceId, $url, $publicId ?: null, $i]);
                }
            }

            $this->db->commit();

            $stmt = $this->db->prepare(
                "SELECT 
                    ps.provider_service_id,
                    ps.provider_id,
                    ps.service_id,
                    ps.price,
                    ps.price_type,
                    ps.is_active,
                    ps.total_bookings,
                    ps.created_at,
                    ps.updated_at,
                    s.service_name,
                    COALESCE(ps.description, s.description) AS description,
                    s.service_image,
                    s.duration_minutes,
                    sc.category_name
                 FROM provider_services ps
                 JOIN services s ON ps.service_id = s.service_id
                 LEFT JOIN service_categories sc ON s.category_id = sc.category_id
                 WHERE ps.provider_service_id = ?"
            );
            $stmt->execute([$providerServiceId]);
            $row = $stmt->fetch() ?: [];

            // Attach images
            $imgStmt = $this->db->prepare(
                "SELECT image_url, public_id, sort_order
                 FROM provider_service_images
                 WHERE provider_service_id = ?
                 ORDER BY sort_order ASC, image_id ASC"
            );
            $imgStmt->execute([$providerServiceId]);
            $imgs = $imgStmt->fetchAll();
            $row['images'] = array_map(function ($img) {
                return [
                    'url' => $img['image_url'] ?? null,
                    'public_id' => $img['public_id'] ?? null,
                    'sort_order' => isset($img['sort_order']) ? (int)$img['sort_order'] : 0,
                ];
            }, $imgs ?: []);

            return $row;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            // MySQL duplicate entry (unique_provider_service) typically uses SQLSTATE 23000
            if ($e->getCode() === '23000') {
                throw new Exception('You already offer this service');
            }
            throw $e;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Get a provider service offering by id (joined with service metadata)
     *
     * @param int $providerServiceId
     * @return array|false
     */
    private function getServiceOfferingById($providerServiceId) {
        $stmt = $this->db->prepare(
            "SELECT 
                ps.provider_service_id,
                ps.provider_id,
                ps.service_id,
                ps.price,
                ps.price_type,
                ps.is_active,
                ps.total_bookings,
                ps.created_at,
                ps.updated_at,
                s.service_name,
                COALESCE(ps.description, s.description) AS description,
                s.service_image,
                s.duration_minutes,
                sc.category_name
             FROM provider_services ps
             JOIN services s ON ps.service_id = s.service_id
             LEFT JOIN service_categories sc ON s.category_id = sc.category_id
             WHERE ps.provider_service_id = ?"
        );
        $stmt->execute([(int)$providerServiceId]);
        $row = $stmt->fetch();
        if (!$row) {
            return $row;
        }

        $imgStmt = $this->db->prepare(
            "SELECT image_url, public_id, sort_order
             FROM provider_service_images
             WHERE provider_service_id = ?
             ORDER BY sort_order ASC, image_id ASC"
        );
        $imgStmt->execute([(int)$providerServiceId]);
        $imgs = $imgStmt->fetchAll();
        $row['images'] = array_map(function ($img) {
            return [
                'url' => $img['image_url'] ?? null,
                'public_id' => $img['public_id'] ?? null,
                'sort_order' => isset($img['sort_order']) ? (int)$img['sort_order'] : 0,
            ];
        }, $imgs ?: []);

        return $row;
    }

    /**
     * Update a provider service offering (price, price_type, description, is_active)
     *
     * @param int $providerId
     * @param int $providerServiceId
     * @param array $data
     * @return array Updated row
     */
    public function updateServiceOffering($providerId, $providerServiceId, $data) {
        $providerServiceId = (int)$providerServiceId;
        $providerId = (int)$providerId;

        $existing = $this->getServiceOfferingById($providerServiceId);
        if (!$existing || (int)$existing['provider_id'] !== $providerId) {
            throw new Exception('Service offering not found');
        }

        $allowed = ['price', 'price_type', 'description', 'is_active'];
        $fields = [];
        $values = [];

        $hasImages = array_key_exists('images', $data);
        $images = $hasImages && is_array($data['images']) ? $data['images'] : [];

        foreach ($allowed as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                if ($field === 'is_active') {
                    $values[] = (bool)$data[$field] ? 1 : 0;
                } else {
                    $values[] = $data[$field];
                }
            }
        }

        if (empty($fields) && !$hasImages) {
            throw new Exception('No changes provided');
        }

        try {
            $this->db->beginTransaction();

            if (!empty($fields)) {
                $values[] = $providerServiceId;
                $values[] = $providerId;

                $sql = "UPDATE provider_services SET " . implode(', ', $fields) . " WHERE provider_service_id = ? AND provider_id = ?";
                $stmt = $this->db->prepare($sql);
                $stmt->execute($values);
            }

            if ($hasImages) {
                // Replace images (max 10)
                $del = $this->db->prepare(
                    "DELETE FROM provider_service_images WHERE provider_service_id = ?"
                );
                $del->execute([$providerServiceId]);

                if (is_array($images) && count($images) > 0) {
                    $max = min(10, count($images));
                    $imgStmt = $this->db->prepare(
                        "INSERT INTO provider_service_images (provider_service_id, image_url, public_id, sort_order)
                         VALUES (?, ?, ?, ?)"
                    );

                    for ($i = 0; $i < $max; $i++) {
                        $img = $images[$i];
                        if (!is_array($img)) continue;
                        $url = trim((string)($img['url'] ?? ''));
                        $publicId = isset($img['public_id']) ? trim((string)$img['public_id']) : null;
                        if ($url === '') continue;
                        $imgStmt->execute([$providerServiceId, $url, $publicId ?: null, $i]);
                    }
                }
            }

            $this->db->commit();
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }

        $updated = $this->getServiceOfferingById($providerServiceId);
        if (!$updated) {
            throw new Exception('Service offering not found');
        }
        return $updated;
    }

    /**
     * Delete a provider service offering
     *
     * @param int $providerId
     * @param int $providerServiceId
     * @return bool
     */
    public function deleteServiceOffering($providerId, $providerServiceId) {
        $providerServiceId = (int)$providerServiceId;
        $providerId = (int)$providerId;

        $existing = $this->getServiceOfferingById($providerServiceId);
        if (!$existing || (int)$existing['provider_id'] !== $providerId) {
            throw new Exception('Service offering not found');
        }

        $serviceId = (int)$existing['service_id'];

        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("DELETE FROM provider_services WHERE provider_service_id = ? AND provider_id = ?");
            $stmt->execute([$providerServiceId, $providerId]);

            if ($stmt->rowCount() === 0) {
                $this->db->rollBack();
                throw new Exception('Service offering not found');
            }

            // Keep lightweight aggregate in sync (avoid going below 0)
            $stmt = $this->db->prepare(
                "UPDATE services 
                 SET total_providers = CASE WHEN total_providers > 0 THEN total_providers - 1 ELSE 0 END
                 WHERE service_id = ?"
            );
            $stmt->execute([$serviceId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
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
