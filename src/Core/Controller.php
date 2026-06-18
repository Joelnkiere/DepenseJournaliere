<?php
namespace App\Core;

abstract class Controller
{
    protected ?int $userId;

    public function __construct()
    {
        Session::start();
        $this->userId = Session::get('user_id');
    }

    protected function view(string $path, array $data = []): void
    {
        extract($data);
        require_once __DIR__ . "/../Views/{$path}.php";
    }

    protected function json(mixed $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function requireAuth(): void
    {
        if (!Session::isLoggedIn()) {
            $this->json(['error' => 'Non authentifié'], 401);
            exit;
        }
    }
}