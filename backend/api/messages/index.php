<?php
/**
 * Messages API Endpoints
 * Messaging endpoints
 * 
 * @author Moueene Development Team
 * @version 1.0.0
 */

$action = $parts[2] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$input = json_decode(file_get_contents('php://input'), true) ?? [];

switch ($action) {
	case '':
	case 'conversations':
		if ($method === 'GET') {
			handleListConversations();
		}
		Response::error('Method not allowed', 405);
		break;

	case 'thread':
		if ($method === 'GET') {
			handleGetThread();
		}
		Response::error('Method not allowed', 405);
		break;

	case 'send':
		if ($method === 'POST') {
			handleSendMessage($input);
		}
		Response::error('Method not allowed', 405);
		break;

	default:
		Response::error('Invalid messages endpoint', 404);
}

function handleListConversations(): void {
	Auth::requireAuth();
	$authUser = Auth::user();
	$selfType = $authUser['user_type'];
	$selfId = (int)$authUser['user_id'];

	$page = max(1, (int)($_GET['page'] ?? 1));
	$limit = min(50, max(1, (int)($_GET['limit'] ?? 20)));
	$offset = ($page - 1) * $limit;

	try {
		$db = Database::getConnection();

		// Count distinct conversations
		$countSql = "SELECT COUNT(*) FROM (
			SELECT
				CASE WHEN sender_type = ? AND sender_id = ? THEN receiver_type ELSE sender_type END AS other_type,
				CASE WHEN sender_type = ? AND sender_id = ? THEN receiver_id ELSE sender_id END AS other_id
			FROM messages
			WHERE
				(sender_type = ? AND sender_id = ? AND is_deleted_by_sender = FALSE)
				OR
				(receiver_type = ? AND receiver_id = ? AND is_deleted_by_receiver = FALSE)
			GROUP BY other_type, other_id
		) conv";

		$stmt = $db->prepare($countSql);
		$stmt->execute([$selfType, $selfId, $selfType, $selfId, $selfType, $selfId, $selfType, $selfId]);
		$total = (int)$stmt->fetchColumn();

		$sql = "SELECT
			conv.other_type,
			conv.other_id,
			conv.last_message_id,
			conv.last_at,
			conv.unread_count,
			lm.subject AS last_subject,
			lm.message AS last_message,
			lm.sender_type AS last_sender_type,
			lm.sender_id AS last_sender_id,
			lm.receiver_type AS last_receiver_type,
			lm.receiver_id AS last_receiver_id,
			u.first_name AS user_first_name,
			u.last_name AS user_last_name,
			u.profile_picture AS user_profile_picture,
			p.business_name AS provider_business_name,
			p.first_name AS provider_first_name,
			p.last_name AS provider_last_name,
			p.profile_picture AS provider_profile_picture
		FROM (
			SELECT
				other_type,
				other_id,
				MAX(message_id) AS last_message_id,
				MAX(created_at) AS last_at,
				SUM(CASE
					WHEN receiver_type = ? AND receiver_id = ? AND is_read = FALSE THEN 1
					ELSE 0
				END) AS unread_count
			FROM (
				SELECT
					message_id,
					sender_type,
					sender_id,
					receiver_type,
					receiver_id,
					created_at,
					is_read,
					CASE WHEN sender_type = ? AND sender_id = ? THEN receiver_type ELSE sender_type END AS other_type,
					CASE WHEN sender_type = ? AND sender_id = ? THEN receiver_id ELSE sender_id END AS other_id
				FROM messages
				WHERE
					(sender_type = ? AND sender_id = ? AND is_deleted_by_sender = FALSE)
					OR
					(receiver_type = ? AND receiver_id = ? AND is_deleted_by_receiver = FALSE)
			) m
			GROUP BY other_type, other_id
			ORDER BY MAX(created_at) DESC
			LIMIT ? OFFSET ?
		) conv
		JOIN messages lm ON lm.message_id = conv.last_message_id
		LEFT JOIN users u ON conv.other_type = 'user' AND u.user_id = conv.other_id
		LEFT JOIN providers p ON conv.other_type = 'provider' AND p.provider_id = conv.other_id
		ORDER BY conv.last_at DESC";

		$params = [
			$selfType,
			$selfId,
			$selfType,
			$selfId,
			$selfType,
			$selfId,
			$selfType,
			$selfId,
			$selfType,
			$selfId,
			$limit,
			$offset,
		];

		$stmt = $db->prepare($sql);
		$stmt->execute($params);
		$rows = $stmt->fetchAll();

		$conversations = array_map(function($r) {
			$otherType = $r['other_type'];
			$otherName = null;
			$otherAvatar = null;
			if ($otherType === 'user') {
				$otherName = trim(($r['user_first_name'] ?? '') . ' ' . ($r['user_last_name'] ?? ''));
				$otherAvatar = $r['user_profile_picture'] ?? null;
			} elseif ($otherType === 'provider') {
				$otherName = $r['provider_business_name'] ?: trim(($r['provider_first_name'] ?? '') . ' ' . ($r['provider_last_name'] ?? ''));
				$otherAvatar = $r['provider_profile_picture'] ?? null;
			}

			return [
				'other_type' => $otherType,
				'other_id' => (int)$r['other_id'],
				'other_name' => $otherName ?: 'Unknown',
				'other_avatar' => $otherAvatar,
				'unread_count' => (int)($r['unread_count'] ?? 0),
				'last' => [
					'message_id' => (int)$r['last_message_id'],
					'subject' => $r['last_subject'],
					'message' => $r['last_message'],
					'created_at' => $r['last_at'],
					'sender_type' => $r['last_sender_type'],
					'sender_id' => (int)$r['last_sender_id'],
					'receiver_type' => $r['last_receiver_type'],
					'receiver_id' => (int)$r['last_receiver_id'],
				],
			];
		}, $rows);

		Response::paginated($conversations, $total, $page, $limit);
	} catch (Exception $e) {
		error_log('[MOUEENE-MESSAGES] Conversations error: ' . $e->getMessage());
		Response::serverError('Failed to load conversations');
	}
}

function handleGetThread(): void {
	Auth::requireAuth();
	$authUser = Auth::user();
	$selfType = $authUser['user_type'];
	$selfId = (int)$authUser['user_id'];

	$withType = strtolower(trim((string)($_GET['with_type'] ?? '')));
	$withId = (int)($_GET['with_id'] ?? 0);

	if (!in_array($withType, ['user', 'provider', 'admin'], true) || $withId <= 0) {
		Response::validationError([
			'with_type' => 'with_type must be user/provider/admin',
			'with_id' => 'with_id must be a positive integer'
		]);
	}

	$limit = min(200, max(1, (int)($_GET['limit'] ?? 50)));

	try {
		$db = Database::getConnection();

		$sql = "SELECT message_id, sender_type, sender_id, receiver_type, receiver_id, booking_id, subject, message, attachment_path,
					   is_read, read_at, created_at
				FROM messages
				WHERE
					(
						sender_type = ? AND sender_id = ? AND receiver_type = ? AND receiver_id = ? AND is_deleted_by_sender = FALSE
					)
					OR
					(
						sender_type = ? AND sender_id = ? AND receiver_type = ? AND receiver_id = ? AND is_deleted_by_receiver = FALSE
					)
				ORDER BY created_at ASC
				LIMIT ?";

		$stmt = $db->prepare($sql);
		$stmt->execute([
			$selfType, $selfId, $withType, $withId,
			$withType, $withId, $selfType, $selfId,
			$limit
		]);
		$messages = $stmt->fetchAll();

		// Mark unread incoming messages as read
		$markSql = "UPDATE messages
					SET is_read = TRUE, read_at = NOW()
					WHERE sender_type = ? AND sender_id = ?
					  AND receiver_type = ? AND receiver_id = ?
					  AND is_read = FALSE";
		$stmt = $db->prepare($markSql);
		$stmt->execute([$withType, $withId, $selfType, $selfId]);

		Response::success([
			'with' => [
				'type' => $withType,
				'id' => $withId,
			],
			'messages' => $messages,
		]);
	} catch (Exception $e) {
		error_log('[MOUEENE-MESSAGES] Thread error: ' . $e->getMessage());
		Response::serverError('Failed to load messages');
	}
}

function handleSendMessage(array $data): void {
	Auth::requireAuth();
	$authUser = Auth::user();
	$selfType = $authUser['user_type'];
	$selfId = (int)$authUser['user_id'];

	$receiverType = strtolower(trim((string)($data['receiver_type'] ?? '')));
	$receiverId = (int)($data['receiver_id'] ?? 0);
	$message = trim((string)($data['message'] ?? ''));
	$subject = isset($data['subject']) ? trim((string)$data['subject']) : null;
	$bookingId = isset($data['booking_id']) ? (int)$data['booking_id'] : null;

	$validator = new Validator([
		'receiver_type' => $receiverType,
		'receiver_id' => $receiverId,
		'message' => $message,
	]);
	$validator
		->required('receiver_type')->in('receiver_type', ['user', 'provider', 'admin'])
		->required('receiver_id')->numeric('receiver_id')
		->required('message')->minLength('message', 1);
	if ($validator->fails()) {
		Response::validationError($validator->getErrors());
	}

	if ($receiverType === $selfType && $receiverId === $selfId) {
		Response::error('Cannot send message to yourself', 400);
	}

	try {
		$db = Database::getConnection();

		// Validate receiver exists
		if ($receiverType === 'user') {
			$stmt = $db->prepare('SELECT user_id FROM users WHERE user_id = ?');
			$stmt->execute([$receiverId]);
			if (!$stmt->fetch()) {
				Response::notFound('User');
			}
		} elseif ($receiverType === 'provider') {
			$stmt = $db->prepare('SELECT provider_id FROM providers WHERE provider_id = ?');
			$stmt->execute([$receiverId]);
			if (!$stmt->fetch()) {
				Response::notFound('Provider');
			}
		} else {
			$stmt = $db->prepare('SELECT admin_id FROM admin_users WHERE admin_id = ?');
			$stmt->execute([$receiverId]);
			if (!$stmt->fetch()) {
				Response::notFound('Admin');
			}
		}

		$sql = "INSERT INTO messages (
					sender_type, sender_id,
					receiver_type, receiver_id,
					booking_id,
					subject,
					message
				) VALUES (?, ?, ?, ?, ?, ?, ?)";
		$stmt = $db->prepare($sql);
		$stmt->execute([
			$selfType,
			$selfId,
			$receiverType,
			$receiverId,
			$bookingId,
			$subject,
			$message,
		]);

		$messageId = (int)$db->lastInsertId();
		$stmt = $db->prepare('SELECT message_id, sender_type, sender_id, receiver_type, receiver_id, booking_id, subject, message, attachment_path, is_read, read_at, created_at FROM messages WHERE message_id = ?');
		$stmt->execute([$messageId]);
		$created = $stmt->fetch();

		Response::success($created, 'Message sent', 201);
	} catch (Exception $e) {
		error_log('[MOUEENE-MESSAGES] Send error: ' . $e->getMessage());
		Response::serverError('Failed to send message');
	}
}
