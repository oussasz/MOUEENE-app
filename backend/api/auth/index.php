<?php
/**
 * Authentication API Endpoints
 * Handles user registration, login, password reset
 * 
 * IMPORTANT: This API uses separate tables for users (customers) and providers.
 * - 'user' type -> users table (user_id)
 * - 'provider' type -> providers table (provider_id)
 * 
 * @author Moueene Development Team
 * @version 2.0.0
 */

// Load required classes
require_once CLASSES_PATH . '/User.php';
require_once CLASSES_PATH . '/Provider.php';

// Get action from URL
$action = $parts[2] ?? '';
$method = $_SERVER['REQUEST_METHOD'];

// Get request body
$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
    case 'register':
        if ($method === 'POST') {
            handleRegister($input);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    case 'register-user':
        if ($method === 'POST') {
            handleRegister($input, 'user');
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    case 'register-provider':
        if ($method === 'POST') {
            handleRegister($input, 'provider');
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'login':
        if ($method === 'POST') {
            handleLogin($input);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    case 'login-user':
        if ($method === 'POST') {
            handleLogin($input, 'user');
        } else {
            Response::error('Method not allowed', 405);
        }
        break;

    case 'login-provider':
        if ($method === 'POST') {
            handleLogin($input, 'provider');
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'logout':
        if ($method === 'POST') {
            handleLogout();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'verify-email':
        if ($method === 'POST') {
            handleVerifyEmail($input);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'forgot-password':
        if ($method === 'POST') {
            handleForgotPassword($input);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'reset-password':
        if ($method === 'POST') {
            handleResetPassword($input);
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    case 'me':
        if ($method === 'GET') {
            handleGetCurrentUser();
        } else {
            Response::error('Method not allowed', 405);
        }
        break;
        
    default:
        Response::error('Invalid auth endpoint', 404);
}

/**
 * ============================================================================
 * REGISTRATION HANDLER
 * ============================================================================
 * 
 * Creates a new account in either 'users' or 'providers' table based on user_type.
 * 
 * Required fields:
 * - email, password, first_name, last_name, user_type
 * - For providers: phone, address, city are also required
 */
function handleRegister($data, $forcedType = null) {
    $rawType = $data['user_type'] ?? $data['account_type'] ?? $data['type'] ?? $data['role'] ?? ($data['is_provider'] ?? null);
    $normalizedType = $forcedType ?? normalizeUserType($data);
    $data['user_type'] = $normalizedType;

    // Log for debugging (can be removed in production)
    error_log('[MOUEENE-AUTH] Registration attempt - raw_type: ' . json_encode($rawType) . ' forced: ' . json_encode($forcedType) . ' normalized: ' . ($normalizedType ?? 'INVALID'));
    
    // Basic validation
    $validator = new Validator($data);
    $validator
        ->required('email')->email('email')
        ->required('password')->minLength('password', 8)
        ->required('first_name')->minLength('first_name', 2)
        ->required('last_name')->minLength('last_name', 2);

    if ($forcedType === null) {
        $validator->required('user_type')->in('user_type', ['user', 'provider']);
    } else {
        if (!in_array($forcedType, ['user', 'provider'], true)) {
            Response::error('Invalid account type.', 400);
            return;
        }
    }
    
    // Provider-specific validation
    $userType = $data['user_type'] ?? null;
    if ($userType === 'provider') {
        $validator
            ->required('phone')->minLength('phone', 6)
            ->required('address')->minLength('address', 2)
            ->required('city')->minLength('city', 2);
    }
    
    if ($validator->fails()) {
        error_log('[MOUEENE-AUTH] Validation failed: ' . json_encode($validator->getErrors()));
        Response::validationError($validator->getErrors());
        return;
    }
    
    try {
        $db = Database::getConnection();
        
        // Normalize email
        $email = strtolower(trim($data['email']));
        
        // Check if email exists in EITHER table (prevent duplicate emails across tables)
        $stmt = $db->prepare("SELECT 'users' AS source FROM users WHERE email = ? UNION SELECT 'providers' AS source FROM providers WHERE email = ?");
        $stmt->execute([$email, $email]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            error_log('[MOUEENE-AUTH] Email already exists in ' . $existing['source'] . ' table');
            Response::error('This email is already registered. Please login instead.', 409);
            return;
        }
        
        // Hash password
        $passwordHash = Auth::hashPassword($data['password']);
        
        // Generate verification token
        $verificationToken = Auth::generateRandomToken();
        
        // Default profile picture
        $defaultAvatar = '/assets/images/default-avatar.jpg';
        
        // Prepare optional fields
        $phone = isset($data['phone']) ? trim($data['phone']) : null;
        $address = isset($data['address']) ? trim($data['address']) : null;
        $city = isset($data['city']) ? trim($data['city']) : null;
        $country = 'Algeria'; // Default country
        
        // =====================================================================
        // INSERT INTO CORRECT TABLE BASED ON USER_TYPE
        // =====================================================================
        
        if ($userType === 'provider') {
            // *** PROVIDER REGISTRATION ***
            error_log('[MOUEENE-AUTH] Creating PROVIDER account for: ' . $email);
            
            $sql = "INSERT INTO providers (
                        email, 
                        password_hash, 
                        first_name, 
                        last_name, 
                        phone, 
                        address, 
                        city, 
                        country, 
                        profile_picture, 
                        verification_token,
                        account_status,
                        verification_status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $email,
                $passwordHash,
                trim($data['first_name']),
                trim($data['last_name']),
                $phone,
                $address,
                $city,
                $country,
                $defaultAvatar,
                $verificationToken
            ]);
            
            $accountId = $db->lastInsertId();
            $idFieldName = 'provider_id';
            
            error_log('[MOUEENE-AUTH] SUCCESS - Created provider_id: ' . $accountId);
            
        } else {
            // *** CUSTOMER (USER) REGISTRATION ***
            error_log('[MOUEENE-AUTH] Creating USER (customer) account for: ' . $email);
            
            $sql = "INSERT INTO users (
                        email, 
                        password_hash, 
                        first_name, 
                        last_name, 
                        phone, 
                        address, 
                        city, 
                        country, 
                        profile_picture, 
                        verification_token,
                        account_status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                $email,
                $passwordHash,
                trim($data['first_name']),
                trim($data['last_name']),
                $phone,
                $address,
                $city,
                $country,
                $defaultAvatar,
                $verificationToken
            ]);
            
            $accountId = $db->lastInsertId();
            $idFieldName = 'user_id';
            
            error_log('[MOUEENE-AUTH] SUCCESS - Created user_id: ' . $accountId);
        }
        
        // Generate JWT token
        $token = Auth::generateToken($accountId, $userType);
        
        // Build response
        $responseData = [
            'token' => $token,
            $idFieldName => $accountId,
            'user_type' => $userType,
            'email' => $email,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name']
        ];
        
        // TODO: Send verification email
        
        Response::success($responseData, 'Account created successfully!', 201);
        
    } catch (PDOException $e) {
        error_log('[MOUEENE-AUTH] Database error: ' . $e->getMessage());
        
        // Check for duplicate entry error
        if ($e->getCode() == 23000) {
            Response::error('This email is already registered.', 409);
        } else {
            Response::serverError('Registration failed. Please try again.');
        }
    } catch (Exception $e) {
        error_log('[MOUEENE-AUTH] Error: ' . $e->getMessage());
        Response::serverError('Registration failed. Please try again.');
    }
}

/**
 * ============================================================================
 * LOGIN HANDLER
 * ============================================================================
 * 
 * Authenticates user against the correct table based on user_type.
 * 
 * IMPORTANT: The user_type MUST match the account type.
 * - If user selects "Customer" but email is in providers table: show helpful error
 * - If user selects "Provider" but email is in users table: show helpful error
 */
function handleLogin($data, $forcedType = null) {
    $normalizedType = $forcedType ?? normalizeUserType($data, true);
    $data['user_type'] = $normalizedType;

    // Validate input
    $validator = new Validator($data);
    $validator
        ->required('email')->email('email')
        ->required('password');

    if ($forcedType === null) {
        $validator->required('user_type')->in('user_type', ['user', 'provider', 'admin']);
    } else {
        if (!in_array($forcedType, ['user', 'provider'], true)) {
            Response::error('Invalid account type.', 400);
            return;
        }
    }
    
    if ($validator->fails()) {
        Response::validationError($validator->getErrors());
        return;
    }
    
    try {
        $db = Database::getConnection();
        
        $email = strtolower(trim($data['email']));
        $password = $data['password'];
        $requestedType = $data['user_type'];
        
        // =====================================================================
        // ADMIN LOGIN
        // =====================================================================
        if ($requestedType === 'admin') {
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            $admin = $stmt->fetch();
            
            if (!$admin) {
                Response::error('Invalid credentials.', 401);
                return;
            }
            
            if (!Auth::verifyPassword($password, $admin['password_hash'])) {
                Response::error('Invalid credentials.', 401);
                return;
            }
            
            // Update last login
            $db->prepare("UPDATE admin_users SET last_login = NOW() WHERE admin_id = ?")
               ->execute([$admin['admin_id']]);
            
            $token = Auth::generateToken($admin['admin_id'], 'admin');
            unset($admin['password_hash']);
            
            Response::success([
                'token' => $token,
                'user_type' => 'admin',
                'user' => $admin
            ], 'Login successful');
            return;
        }
        
        // =====================================================================
        // CUSTOMER / PROVIDER LOGIN
        // =====================================================================
        
        if ($requestedType === 'provider') {
            $table = 'providers';
            $idField = 'provider_id';
            $friendlyName = 'Provider';
            $oppositeTable = 'users';
            $oppositeName = 'Customer';
        } else {
            $table = 'users';
            $idField = 'user_id';
            $friendlyName = 'Customer';
            $oppositeTable = 'providers';
            $oppositeName = 'Provider';
        }
        
        // Try to find user in the requested table
        $stmt = $db->prepare("SELECT * FROM $table WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if (!$user) {
            // Email not found in requested table - check if it exists in the other table
            $stmt = $db->prepare("SELECT 1 FROM $oppositeTable WHERE email = ?");
            $stmt->execute([$email]);
            $existsInOther = $stmt->fetch();
            
            if ($existsInOther) {
                // Email exists but in wrong table - give helpful message
                Response::error(
                    "This email is registered as a $oppositeName account. Please switch to $oppositeName login.",
                    400
                );
            } else {
                // Email doesn't exist anywhere
                Response::error("No account found with this email address.", 404);
            }
            return;
        }
        
        // Verify password
        if (!Auth::verifyPassword($password, $user['password_hash'])) {
            Response::error('Invalid email or password.', 401);
            return;
        }
        
        // Check account status
        $status = $user['account_status'] ?? 'active';
        $blockedStatuses = ['suspended', 'deactivated', 'deleted', 'inactive'];
        if (in_array($status, $blockedStatuses, true)) {
            Response::error('Your account has been ' . $status . '. Please contact support.', 403);
            return;
        }
        
        // Update last login
        $db->prepare("UPDATE $table SET last_login = NOW() WHERE $idField = ?")
           ->execute([$user[$idField]]);
        
        // Generate JWT token
        $token = Auth::generateToken($user[$idField], $requestedType);
        
        // Remove sensitive data before sending response
        unset($user['password_hash'], $user['reset_token'], $user['verification_token'], $user['two_factor_secret']);
        
        Response::success([
            'token' => $token,
            'user_type' => $requestedType,
            'user' => $user
        ], 'Login successful');
        
    } catch (Exception $e) {
        error_log('[MOUEENE-AUTH] Login error: ' . $e->getMessage());
        Response::serverError('Login failed. Please try again.');
    }
}

/**
 * Handle logout
 */
function handleLogout() {
    Auth::requireAuth();
    Response::success(null, 'Logged out successfully');
}

/**
 * Handle email verification
 */
function handleVerifyEmail($data) {
    $validator = new Validator($data);
    $validator->required('token');
    
    if ($validator->fails()) {
        Response::validationError($validator->getErrors());
        return;
    }
    
    try {
        $db = Database::getConnection();
        $token = trim($data['token']);
        
        // Try users table first
        $stmt = $db->prepare("SELECT user_id FROM users WHERE verification_token = ?");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $db->prepare("UPDATE users SET email_verified = TRUE, verification_token = NULL WHERE user_id = ?")
               ->execute([$user['user_id']]);
            Response::success(null, 'Email verified successfully');
            return;
        }
        
        // Try providers table
        $stmt = $db->prepare("SELECT provider_id FROM providers WHERE verification_token = ?");
        $stmt->execute([$token]);
        $provider = $stmt->fetch();
        
        if ($provider) {
            $db->prepare("UPDATE providers SET email_verified = TRUE, verification_token = NULL WHERE provider_id = ?")
               ->execute([$provider['provider_id']]);
            Response::success(null, 'Email verified successfully');
            return;
        }
        
        Response::error('Invalid or expired verification token.', 400);
        
    } catch (Exception $e) {
        error_log('[MOUEENE-AUTH] Verification error: ' . $e->getMessage());
        Response::serverError('Verification failed. Please try again.');
    }
}

/**
 * Handle forgot password request
 */
function handleForgotPassword($data) {
    $validator = new Validator($data);
    $validator->required('email')->email('email');
    
    if ($validator->fails()) {
        Response::validationError($validator->getErrors());
        return;
    }
    
    try {
        $db = Database::getConnection();
        $email = strtolower(trim($data['email']));
        
        // Check users table
        $stmt = $db->prepare("SELECT user_id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user) {
            $resetToken = Auth::generateRandomToken();
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?")
               ->execute([$resetToken, $expiry, $user['user_id']]);
            
            // TODO: Send password reset email
        }
        
        // Check providers table
        $stmt = $db->prepare("SELECT provider_id FROM providers WHERE email = ?");
        $stmt->execute([$email]);
        $provider = $stmt->fetch();
        
        if ($provider) {
            $resetToken = Auth::generateRandomToken();
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $db->prepare("UPDATE providers SET reset_token = ?, reset_token_expires = ? WHERE provider_id = ?")
               ->execute([$resetToken, $expiry, $provider['provider_id']]);
            
            // TODO: Send password reset email
        }
        
        // Always return success to prevent email enumeration
        Response::success(null, 'If an account exists with this email, a password reset link has been sent.');
        
    } catch (Exception $e) {
        error_log('[MOUEENE-AUTH] Forgot password error: ' . $e->getMessage());
        Response::serverError('Request failed. Please try again.');
    }
}

/**
 * Handle password reset
 */
function handleResetPassword($data) {
    $validator = new Validator($data);
    $validator
        ->required('token')
        ->required('password')->minLength('password', 8);
    
    if ($validator->fails()) {
        Response::validationError($validator->getErrors());
        return;
    }
    
    try {
        $db = Database::getConnection();
        $token = trim($data['token']);
        $passwordHash = Auth::hashPassword($data['password']);
        
        // Try users table
        $stmt = $db->prepare("SELECT user_id FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch();
        
        if ($user) {
            $db->prepare("UPDATE users SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE user_id = ?")
               ->execute([$passwordHash, $user['user_id']]);
            Response::success(null, 'Password reset successful. You can now login with your new password.');
            return;
        }
        
        // Try providers table
        $stmt = $db->prepare("SELECT provider_id FROM providers WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->execute([$token]);
        $provider = $stmt->fetch();
        
        if ($provider) {
            $db->prepare("UPDATE providers SET password_hash = ?, reset_token = NULL, reset_token_expires = NULL WHERE provider_id = ?")
               ->execute([$passwordHash, $provider['provider_id']]);
            Response::success(null, 'Password reset successful. You can now login with your new password.');
            return;
        }
        
        Response::error('Invalid or expired reset token.', 400);
        
    } catch (Exception $e) {
        error_log('[MOUEENE-AUTH] Password reset error: ' . $e->getMessage());
        Response::serverError('Password reset failed. Please try again.');
    }
}

/**
 * Get current authenticated user
 */
function handleGetCurrentUser() {
    Auth::requireAuth();
    $authUser = Auth::user();
    
    try {
        $db = Database::getConnection();
        $userType = $authUser['user_type'];
        $userId = $authUser['user_id'];
        
        if ($userType === 'admin') {
            $stmt = $db->prepare("SELECT * FROM admin_users WHERE admin_id = ?");
            $stmt->execute([$userId]);
        } elseif ($userType === 'provider') {
            $stmt = $db->prepare("SELECT * FROM providers WHERE provider_id = ?");
            $stmt->execute([$userId]);
        } else {
            $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->execute([$userId]);
        }
        
        $user = $stmt->fetch();
        
        if (!$user) {
            Response::notFound('User not found');
            return;
        }
        
        // Remove sensitive data
        unset($user['password_hash'], $user['reset_token'], $user['verification_token'], $user['two_factor_secret']);
        
        // Add user_type to response
        $user['user_type'] = $userType;
        
        Response::success($user);
        
    } catch (Exception $e) {
        error_log('[MOUEENE-AUTH] Get user error: ' . $e->getMessage());
        Response::serverError('Failed to get user data.');
    }
}

/**
 * Normalize user_type value from various client payloads.
 * Accepts legacy fields like account_type/type/role/is_provider
 * and maps common labels (customer/client) to 'user'.
 */
function normalizeUserType(array $data, bool $allowAdmin = false): ?string {
    $raw = $data['user_type'] ?? $data['account_type'] ?? $data['type'] ?? $data['role'] ?? null;

    if ($raw === null && array_key_exists('is_provider', $data)) {
        $isProvider = $data['is_provider'];
        if ($isProvider === true || $isProvider === 1 || $isProvider === '1' || $isProvider === 'true') {
            $raw = 'provider';
        }
    }

    if (is_string($raw)) {
        $raw = strtolower(trim($raw));
    }

    if ($allowAdmin && $raw === 'admin') {
        return 'admin';
    }

    $map = [
        'user' => 'user',
        'users' => 'user',
        'customer' => 'user',
        'client' => 'user',
        'customer_account' => 'user',
        'provider' => 'provider',
        'providers' => 'provider',
        'service_provider' => 'provider',
        'service-provider' => 'provider',
        'vendor' => 'provider',
        'professional' => 'provider',
        'freelancer' => 'provider',
    ];

    if (is_string($raw) && isset($map[$raw])) {
        return $map[$raw];
    }

    return null;
}
