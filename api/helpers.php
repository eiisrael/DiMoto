<?php
declare(strict_types=1);

function json_input(): array {
  $raw = file_get_contents('php://input') ?: '';
  $data = json_decode($raw, true);
  return is_array($data) ? $data : [];
}
function respond($data, int $code = 200): void {
  http_response_code($code);
  echo json_encode($data);
  exit;
}
function sanitize_email(string $email): string {
  return filter_var(trim($email), FILTER_VALIDATE_EMAIL) ? strtolower(trim($email)) : '';
}
