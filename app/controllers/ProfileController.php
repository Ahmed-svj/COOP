<?php
namespace Controllers;

use Auth;
use Controller;
use Database;
use Upload;

class ProfileController extends Controller
{
    public function settings(): void
    {
        $prefs = [
            'email_notifications' => pref('email_notifications', true),
            'dark_mode'           => current_theme() === 'dark',
        ];
        $this->view('shared/settings', compact('prefs'));
    }

    public function saveSettings(): void
    {
        $id = Auth::id();
        \UserPreference::set($id, 'email_notifications', !empty($_POST['email_notifications']));
        \UserPreference::set($id, 'dark_mode', !empty($_POST['dark_mode']));
        $phone = trim((string) ($_POST['phone'] ?? ''));
        if ($phone !== '') {
            if (!preg_match('/^05[0-9]{8}$/', $phone)) {
                \Session::flash('errors', ['phone' => __('v.phone')]);
                $this->redirect('/settings');
            }
            Database::query('UPDATE users SET Phone = ? WHERE UserID = ?', [$phone, $id]);
        }
        flash('success', __('settings.saved'));
        $this->redirect('/settings');
    }

    public function changePassword(): void
    {
        $user = Auth::user();
        if (!password_verify((string) ($_POST['current_password'] ?? ''), $user['PasswordHash'])) {
            \Session::flash('errors', ['current_password' => __('settings.wrong_password')]);
            $this->redirect('/settings');
        }
        $d = $this->validate(['password' => 'required|password|confirmed']);
        Database::query('UPDATE users SET PasswordHash = ? WHERE UserID = ?', [password_hash($d['password'], PASSWORD_BCRYPT), $user['UserID']]);
        \Session::regenerate();
        \AuditService::log('PASSWORD_CHANGED', 'User', (int) $user['UserID']);
        flash('success', __('settings.password_changed'));
        $this->redirect('/settings');
    }

    /** Change the sign-in email: current password required, unique, students keep a college address. */
    public function changeEmail(): void
    {
        $user = Auth::user();
        if (!password_verify((string) ($_POST['email_password'] ?? ''), $user['PasswordHash'])) {
            \Session::flash('errors', ['email_password' => __('settings.wrong_password')]);
            \Session::flash('old', ['new_email' => $_POST['new_email'] ?? '']);
            $this->redirect('/settings');
        }
        $_POST['new_email'] = strtolower(trim((string) ($_POST['new_email'] ?? '')));
        $rules = 'required|email|max:190|unique:users,Email';
        if ($user['Role'] === 'Student') {
            $rules = 'required|email|student_email|max:190|unique:users,Email';
        }
        $d = $this->validate(['new_email' => $rules]);
        $old = $user['Email'];
        Database::query('UPDATE users SET Email = ? WHERE UserID = ?', [$d['new_email'], $user['UserID']]);
        \Session::regenerate();
        \AuditService::log('EMAIL_CHANGED', 'User', (int) $user['UserID'], $old . ' -> ' . $d['new_email']);
        \MailService::send($old, __('settings.email_changed_subject'), __('settings.email_changed_body', ['email' => $d['new_email']]));
        flash('success', __('settings.email_changed', ['email' => $d['new_email']]));
        $this->redirect('/settings');
    }

    public function avatar(): void
    {
        $ajax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']);
        [$path, $err] = Upload::store($_FILES['avatar'] ?? null, 'avatars', Upload::IMAGES, 2 * 1024 * 1024);
        if ($err && $ajax) {
            $this->json(['ok' => false, 'error' => $err], 422);
        }
        if ($err) {
            flash('error', $err);
            $this->redirect('/settings');
        }
        Upload::delete(Auth::user()['AvatarPhoto']);
        Database::query('UPDATE users SET AvatarPhoto = ? WHERE UserID = ?', [$path, Auth::id()]);
        \AuditService::log('AVATAR_CHANGED', 'User', Auth::id());
        if ($ajax) {
            $this->json(['ok' => true, 'url' => url('/avatar/' . Auth::id(), ['v' => substr(md5($path), 0, 8)]), 'message' => __('settings.avatar_saved')]);
        }
        flash('success', __('settings.avatar_saved'));
        $this->redirect('/settings');
    }

    public function showAvatar(int $id): void
    {
        $path = Database::value('SELECT AvatarPhoto FROM users WHERE UserID = ?', [$id]);
        if (!$path) {
            abort(404);
        }
        header('Cache-Control: private, max-age=86400');
        Upload::send($path, 'avatar');
    }

    /** CV download: the student, a company they applied to, their supervisor, or an admin. */
    public function downloadCv(int $studentId): void
    {
        $s = \Student::withUser($studentId);
        if (!$s || !$s['CVFilePath']) {
            abort(404);
        }
        $p = Auth::profile();
        $allowed = match (Auth::role()) {
            'Student'    => (int) $p['StudentID'] === $studentId,
            'Company'    => (bool) Database::value(
                'SELECT 1 FROM applications a JOIN coop_opportunities o ON o.OpportunityID = a.OpportunityID WHERE a.StudentID = ? AND o.CompanyID = ?',
                [$studentId, $p['CompanyID']]
            ),
            'Supervisor' => (int) $s['SupervisorID'] === (int) $p['SupervisorID'],
            'Admin'      => true,
        };
        if (!$allowed) {
            abort(403);
        }
        Upload::send($s['CVFilePath'], 'CV_' . $s['InstitutionalID']);
    }

    public function downloadReport(int $reportId): void
    {
        $r = Database::one('SELECT fr.*, s.InstitutionalID FROM final_reports fr JOIN students s ON s.StudentID = fr.StudentID WHERE fr.ReportID = ?', [$reportId]);
        if (!$r) {
            abort(404);
        }
        $p = Auth::profile();
        $allowed = match (Auth::role()) {
            'Student'    => (int) $p['StudentID'] === (int) $r['StudentID'],
            'Supervisor' => (int) $p['SupervisorID'] === (int) $r['SupervisorID'],
            'Admin'      => true,
            default      => false,
        };
        if (!$allowed) {
            abort(403);
        }
        Upload::send($r['FilePath'], 'Report_' . $r['InstitutionalID']);
    }
}
