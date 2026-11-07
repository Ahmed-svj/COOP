<?php
class NotificationService
{
    /** Create an in-app notification and, if the user wants it, an email. */
    public static function notify(int $userId, string $message, ?string $link = null): void
    {
        Database::query('INSERT INTO notifications (UserID, Message, Link) VALUES (?, ?, ?)', [$userId, $message, $link]);

        $wantsEmail = Database::value(
            "SELECT Enabled FROM user_preferences WHERE UserID = ? AND PreferenceKey = 'email_notifications'",
            [$userId]
        );
        if ($wantsEmail === null || (int) $wantsEmail === 1) {
            $email = Database::value('SELECT Email FROM users WHERE UserID = ?', [$userId]);
            if ($email) {
                MailService::send($email, 'COOP: ' . mb_substr($message, 0, 60), $message . ($link ? "\n\n" . full_url($link) : ''));
            }
        }
    }

    /** Notify every active admin. */
    public static function admins(string $message, ?string $link = null): void
    {
        foreach (Database::all("SELECT UserID FROM users WHERE Role = 'Admin' AND AccountStatus = 'Active'") as $a) {
            self::notify((int) $a['UserID'], $message, $link);
        }
    }
}
