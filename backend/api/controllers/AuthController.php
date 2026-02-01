<?php

require_once CLASSES_PATH . '/User.php';
require_once CLASSES_PATH . '/Provider.php';

class AuthController {
    public function registerLegacy(Request $req): void {
        $type = $this->inferAccountType($req->json);
        if ($type === null) {
            Response::error('Account type is required. Please use /v1/auth/register-user or /v1/auth/register-provider.', 400);
            return;
        }

        $this->register($req, $type);
    }

    public function loginLegacy(Request $req): void {
        $type = $this->inferAccountType($req->json);
        if ($type === null) {
            Response::error('Account type is required. Please use /v1/auth/login-user or /v1/auth/login-provider.', 400);
            return;
        }

        if ($type === 'admin') {
            $this->loginAdmin($req);
            return;
        }

        $this->login($req, $type);
    }

    public function registerUser(Request $req): void {
        $this->register($req, 'user');
    }

    public function registerProvider(Request $req): void {
        $this->register($req, 'provider');
    }

    public function loginUser(Request $req): void {
        $this->login($req, 'user');
    }

    public function loginProvider(Request $req): void {
        $this->login($req, 'provider');
    }

    private function inferAccountType(array $data): ?string {
        $candidates = [
            $data['user_type'] ?? null,
            $data['account_type'] ?? null,
            $data['type'] ?? null,
            $data['role'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!is_string($candidate)) {
                continue;
            }
            $t = strtolower(trim($candidate));
            if ($t === 'provider' || $t === 'pro') {
                return 'provider';
            }
            if ($t === 'user' || $t === 'customer' || $t === 'client') {
                return 'user';
            }
            if ($t === 'admin' || $t === 'administrator') {
                return 'admin';
            }
        }

        // Support boolean-ish field used by some older clients
        if (array_key_exists('is_provider', $data)) {
            $v = $data['is_provider'];
            if ($v === true || $v === 1 || $v === '1' || $v === 'true') {
                return 'provider';
            }
            if ($v === false || $v === 0 || $v === '0' || $v === 'false') {
                return 'user';
            }
        }

        return null;
    }

    private function loginAdmin(Request $req): void {
        $data = $req->json;

        $validator = new Validator($data);
        $validator
            ->required('email')->email('email')
            ->required('password');

        if ($validator->fails()) {
            Response::validationError($validator->getErrors());
            return;
        }

        $db = Database::getConnection();
        if ($db === null) {
            Response::serverError('Database connection failed');
            return;
        }

        if (!$this->assertExpectedTable($db, 'admin_users', ['admin_id', 'email', 'password_hash'])) {
            return;
        }

        $email = strtolower(trim($data['email']));
        $password = $data['password'];

        $stmt = $db->prepare('SELECT * FROM admin_users WHERE email = ?');
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

        $db->prepare('UPDATE admin_users SET last_login = NOW() WHERE admin_id = ?')
           ->execute([$admin['admin_id']]);

        $token = Auth::generateToken($admin['admin_id'], 'admin');
        unset($admin['password_hash']);

        Response::success([
            'token' => $token,
            'user_type' => 'admin',
            'user' => $admin
        ], 'Login successful');
    }

    private function register(Request $req, string $forcedType): void {
        $data = $req->json;

        $validator = new Validator($data);
        $validator
            ->required('email')->email('email')
            ->required('password')->minLength('password', 8)
            ->required('first_name')->minLength('first_name', 2)
            ->required('last_name')->minLength('last_name', 2);

        if ($forcedType === 'provider') {
            $validator
                ->required('phone')->minLength('phone', 6)
                ->required('address')->minLength('address', 2)
                ->required('city')->minLength('city', 2)
                ->required('provider_type')->in('provider_type', ['freelancer', 'self_employed', 'company']);
        }

        if ($validator->fails()) {
            Response::validationError($validator->getErrors());
            return;
        }

        $db = Database::getConnection();
        if ($db === null) {
            Response::serverError('Database connection failed');
            return;
        }

        if ($forcedType === 'provider') {
            if (!$this->assertExpectedTable($db, 'providers', ['provider_id', 'email', 'password_hash'])) {
                return;
            }
        } else {
            if (!$this->assertExpectedTable($db, 'users', ['user_id', 'email', 'password_hash'])) {
                return;
            }
        }

        $email = strtolower(trim($data['email']));

        // Prevent duplicate emails across both tables
        $stmt = $db->prepare("SELECT 'users' AS source FROM users WHERE email = ? UNION SELECT 'providers' AS source FROM providers WHERE email = ?");
        $stmt->execute([$email, $email]);
        $existing = $stmt->fetch();
        if ($existing) {
            Response::error('This email is already registered. Please login instead.', 409);
            return;
        }

        $passwordHash = Auth::hashPassword($data['password']);
        $verificationToken = Auth::generateRandomToken();
        $defaultAvatar = '/assets/images/default-avatar.jpg';

        $phone = isset($data['phone']) ? trim($data['phone']) : null;
        $address = isset($data['address']) ? trim($data['address']) : null;
        $city = isset($data['city']) ? trim($data['city']) : null;
        $country = isset($data['country']) && trim($data['country']) !== '' ? trim($data['country']) : 'Algeria';

        $businessName = isset($data['business_name']) ? trim($data['business_name']) : null;
        $bio = isset($data['bio']) ? trim($data['bio']) : null;
        $state = isset($data['state']) ? trim($data['state']) : null;
        $zipCode = isset($data['zip_code']) ? trim($data['zip_code']) : null;
        $dateOfBirth = isset($data['date_of_birth']) ? trim($data['date_of_birth']) : null;
        $gender = isset($data['gender']) ? trim($data['gender']) : null;
        $providerType = isset($data['provider_type']) ? trim($data['provider_type']) : 'freelancer';
        $experienceYears = isset($data['experience_years']) ? (int)$data['experience_years'] : null;
        $certification = isset($data['certification']) ? trim($data['certification']) : null;
        $specialization = isset($data['specialization']) ? trim($data['specialization']) : null;
        $languagesSpoken = isset($data['languages_spoken']) ? trim($data['languages_spoken']) : null;
        $serviceRadius = isset($data['service_radius']) ? (int)$data['service_radius'] : null;
        $commercialRegistryNumber = isset($data['commercial_registry_number']) ? trim($data['commercial_registry_number']) : null;
        $nif = isset($data['nif']) ? trim($data['nif']) : null;
        $nis = isset($data['nis']) ? trim($data['nis']) : null;

        if ($forcedType === 'provider') {
            $db->beginTransaction();
            try {
            $sql = "INSERT INTO providers (
                        email,
                        password_hash,
                        business_name,
                        first_name,
                        last_name,
                        phone,
                        profile_picture,
                        bio,
                        address,
                        city,
                        state,
                        zip_code,
                        country,
                        date_of_birth,
                        gender,
                        experience_years,
                        certification,
                        specialization,
                        languages_spoken,
                        service_radius,
                        commercial_registry_number,
                        nif,
                        nis,
                        provider_type,
                        verification_token,
                        account_status,
                        verification_status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', 'pending')";

            $stmt = $db->prepare($sql);
            $stmt->execute([
                $email,
                $passwordHash,
                $businessName,
                trim($data['first_name']),
                trim($data['last_name']),
                $phone,
                $defaultAvatar,
                $bio,
                $address,
                $city,
                $state,
                $zipCode,
                $country,
                $dateOfBirth,
                $gender,
                $experienceYears,
                $certification,
                $specialization,
                $languagesSpoken,
                $serviceRadius,
                $commercialRegistryNumber,
                $nif,
                $nis,
                $providerType,
                $verificationToken
            ]);

            $accountId = $db->lastInsertId();

            // If a DB trigger/misconfiguration inserts into users, we must not allow it.
            $checkUser = $db->prepare('SELECT user_id FROM users WHERE email = ? LIMIT 1');
            $checkUser->execute([$email]);
            $unexpectedUser = $checkUser->fetch();
            if ($unexpectedUser) {
                $db->rollBack();
                Response::serverError(
                    'Database misconfiguration: provider registration is creating rows in users (likely a trigger). Remove triggers on providers/users that insert into users, then try again.'
                );
                return;
            }

            // Confirm the provider row exists before committing
            $checkProvider = $db->prepare('SELECT provider_id FROM providers WHERE provider_id = ? LIMIT 1');
            $checkProvider->execute([$accountId]);
            if (!$checkProvider->fetch()) {
                $db->rollBack();
                Response::serverError('Database error: provider record not found after insert');
                return;
            }

            $db->commit();
            } catch (Exception $e) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                throw $e;
            }
            $token = Auth::generateToken($accountId, 'provider');

            Response::success([
                'token' => $token,
                'provider_id' => (string)$accountId,
                'user_type' => 'provider',
                'created_in' => 'providers',
                'email' => $email,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
            ], 'Account created successfully!', 201);
            return;
        }

        // user
        $db->beginTransaction();
        try {
        $sql = "INSERT INTO users (
                    email,
                    password_hash,
                    first_name,
                    last_name,
                    phone,
                    address,
                    city,
                    state,
                    zip_code,
                    country,
                    date_of_birth,
                    gender,
                    profile_picture,
                    verification_token,
                    account_status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $email,
            $passwordHash,
            trim($data['first_name']),
            trim($data['last_name']),
            $phone,
            $address,
            $city,
            $state,
            $zipCode,
            $country,
            $dateOfBirth,
            $gender,
            $defaultAvatar,
            $verificationToken
        ]);

        $accountId = $db->lastInsertId();

        // Defensive: if a DB trigger/misconfiguration inserts into providers, block it.
        $checkProvider = $db->prepare('SELECT provider_id FROM providers WHERE email = ? LIMIT 1');
        $checkProvider->execute([$email]);
        $unexpectedProvider = $checkProvider->fetch();
        if ($unexpectedProvider) {
            $db->rollBack();
            Response::serverError(
                'Database misconfiguration: user registration is creating rows in providers (likely a trigger). Remove triggers that insert into providers, then try again.'
            );
            return;
        }

        $checkUser = $db->prepare('SELECT user_id FROM users WHERE user_id = ? LIMIT 1');
        $checkUser->execute([$accountId]);
        if (!$checkUser->fetch()) {
            $db->rollBack();
            Response::serverError('Database error: user record not found after insert');
            return;
        }

        $db->commit();
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            throw $e;
        }
        $token = Auth::generateToken($accountId, 'user');

        Response::success([
            'token' => $token,
            'user_id' => (string)$accountId,
            'user_type' => 'user',
            'created_in' => 'users',
            'email' => $email,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
        ], 'Account created successfully!', 201);
    }

    private function login(Request $req, string $forcedType): void {
        $data = $req->json;

        $validator = new Validator($data);
        $validator
            ->required('email')->email('email')
            ->required('password');

        if ($validator->fails()) {
            Response::validationError($validator->getErrors());
            return;
        }

        $db = Database::getConnection();
        if ($db === null) {
            Response::serverError('Database connection failed');
            return;
        }

        if ($forcedType === 'provider') {
            if (!$this->assertExpectedTable($db, 'providers', ['provider_id', 'email', 'password_hash'])) {
                return;
            }
        } else {
            if (!$this->assertExpectedTable($db, 'users', ['user_id', 'email', 'password_hash'])) {
                return;
            }
        }

        $email = strtolower(trim($data['email']));
        $password = $data['password'];

        if ($forcedType === 'provider') {
            $table = 'providers';
            $idField = 'provider_id';
            $oppositeTable = 'users';
            $oppositeName = 'Customer';
        } else {
            $table = 'users';
            $idField = 'user_id';
            $oppositeTable = 'providers';
            $oppositeName = 'Provider';
        }

        $stmt = $db->prepare("SELECT * FROM {$table} WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user) {
            $stmt = $db->prepare("SELECT 1 FROM {$oppositeTable} WHERE email = ?");
            $stmt->execute([$email]);
            $existsInOther = $stmt->fetch();

            if ($existsInOther) {
                Response::error(
                    "This email is registered as a {$oppositeName} account. Please switch to {$oppositeName} login.",
                    400
                );
            } else {
                Response::error('No account found with this email address.', 404);
            }
            return;
        }

        if (!Auth::verifyPassword($password, $user['password_hash'])) {
            Response::error('Invalid email or password.', 401);
            return;
        }

        $status = $user['account_status'] ?? 'active';
        $blocked = ['suspended', 'deactivated', 'deleted', 'inactive'];
        if (in_array($status, $blocked, true)) {
            Response::error('Your account has been ' . $status . '. Please contact support.', 403);
            return;
        }

        $db->prepare("UPDATE {$table} SET last_login = NOW() WHERE {$idField} = ?")
           ->execute([$user[$idField]]);

        $token = Auth::generateToken($user[$idField], $forcedType);

        unset($user['password_hash'], $user['reset_token'], $user['verification_token'], $user['two_factor_secret']);

        Response::success([
            'token' => $token,
            'user_type' => $forcedType,
            'user' => $user
        ], 'Login successful');
    }

    private function assertExpectedTable($db, string $table, array $requiredColumns): bool {
        try {
            $stmt = $db->prepare("SELECT TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
            $stmt->execute([$table]);
            $row = $stmt->fetch();
            if (!$row) {
                Response::serverError("Database schema error: missing table '{$table}'.");
                return false;
            }

            $type = strtoupper((string)($row['TABLE_TYPE'] ?? ''));
            if ($type !== 'BASE TABLE') {
                Response::serverError("Database schema error: '{$table}' is not a BASE TABLE (it is '{$type}'). Please fix your database schema.");
                return false;
            }

            $missing = [];
            foreach ($requiredColumns as $col) {
                $c = $db->prepare("SELECT 1 FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
                $c->execute([$table, $col]);
                if (!$c->fetch()) {
                    $missing[] = $col;
                }
            }

            if (!empty($missing)) {
                Response::serverError("Database schema error: '{$table}' missing columns: " . implode(', ', $missing));
                return false;
            }

            return true;
        } catch (Exception $e) {
            Response::serverError('Database schema check failed');
            return false;
        }
    }
}
