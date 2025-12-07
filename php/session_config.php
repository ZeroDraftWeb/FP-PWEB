<?php
// Custom Session Handler using PDO
class PdoSessionHandler implements SessionHandlerInterface {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function open($savePath, $sessionName): bool {
        return true;
    }

    public function close(): bool {
        return true;
    }

    public function read($id): string|false {
        $stmt = $this->pdo->prepare("SELECT data FROM sessions WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            return $row['data'];
        }
        return '';
    }

    public function write($id, $data): bool {
        $timestamp = time();
        $stmt = $this->pdo->prepare("REPLACE INTO sessions (id, data, timestamp) VALUES (:id, :data, :timestamp)");
        return $stmt->execute([
            ':id' => $id,
            ':data' => $data,
            ':timestamp' => $timestamp
        ]);
    }

    public function destroy($id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function gc($maxlifetime): int|false {
        $stmt = $this->pdo->prepare("DELETE FROM sessions WHERE timestamp < :timestamp");
        $stmt->execute([':timestamp' => time() - $maxlifetime]);
        return $stmt->rowCount();
    }
}

// Initialize Session Handler
$handler = new PdoSessionHandler($pdo);
session_set_save_handler($handler, true);

// Configure session cookies
session_set_cookie_params([
    'lifetime' => 86400, // 24 hours
    'path' => '/',
    'domain' => getenv('COOKIE_DOMAIN') ?: '', // Set this in Vercel if needed
    'secure' => true, // Always true for production (Vercel is HTTPS)
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();
?>
