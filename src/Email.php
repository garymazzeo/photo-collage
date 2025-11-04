<?php
declare(strict_types=1);

final class Email
{
  public static function send(string $to, string $subject, string $body): bool
  {
    $headers = [
      'From: ' . (env('MAIL_FROM', 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost'))),
      'Reply-To: ' . (env('MAIL_REPLY_TO', env('MAIL_FROM', 'noreply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost')))),
      'Content-Type: text/html; charset=UTF-8',
      'X-Mailer: PHP/' . phpversion(),
    ];
    return @mail($to, $subject, $body, implode("\r\n", $headers));
  }

  public static function sendPasswordReset(string $email, string $token): bool
  {
    $appUrl = env('APP_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'));
    $resetUrl = rtrim($appUrl, '/') . '/reset-password?token=' . urlencode($token);
    $subject = 'Password Reset - Photo Collage';
    $body = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family: sans-serif; line-height: 1.6;">
  <h2>Password Reset Request</h2>
  <p>You requested a password reset for your Photo Collage account.</p>
  <p><strong>Reset link:</strong> <a href="{$resetUrl}">{$resetUrl}</a></p>
  <p>Or use this token manually: <code>{$token}</code></p>
  <p>This link expires in 1 hour.</p>
  <p>If you didn't request this, you can safely ignore this email.</p>
</body>
</html>
HTML;
    return self::send($email, $subject, $body);
  }

  public static function test(): bool
  {
    $testEmail = env('MAIL_TEST', 'test@example.com');
    return self::send($testEmail, 'Test Email', '<p>This is a test email from Photo Collage.</p>');
  }
}

