<?php
namespace Controllers\Admin;

use Auth;
use Database;

class UserController extends Base
{
    /** UC-18 */
    public function index(): void
    {
        $role = in_array($_GET['role'] ?? '', ['Student', 'Supervisor', 'Company', 'Admin'], true) ? $_GET['role'] : '';
        $q = mb_substr(trim((string) ($_GET['q'] ?? '')), 0, 100);
        $where = ['1=1'];
        $params = [];
        if ($role) {
            $where[] = 'Role = ?';
            $params[] = $role;
        }
        if ($q !== '') {
            $where[] = '(FullName LIKE ? OR Email LIKE ?)';
            array_push($params, "%$q%", "%$q%");
        }
        [$offset, $per, $page] = paginate(20);
        $total = (int) Database::value('SELECT COUNT(*) FROM users WHERE ' . implode(' AND ', $where), $params);
        $users = Database::all('SELECT * FROM users WHERE ' . implode(' AND ', $where) . " ORDER BY CreatedAt DESC LIMIT $per OFFSET $offset", $params);
        $this->view('admin/users', compact('users', 'role', 'q', 'total', 'page', 'per'));
    }

    public function toggle(int $id): void
    {
        if ($id === Auth::id()) {
            flash('error', __('admin.no_self_suspend'));
            $this->back();
        }
        $u = \User::find($id);
        if (!$u) {
            abort(404);
        }
        $new = $u['AccountStatus'] === 'Active' ? 'Suspended' : 'Active';
        \User::update($id, ['AccountStatus' => $new]);
        \AuditService::log($new === 'Suspended' ? 'USER_SUSPENDED' : 'USER_ACTIVATED', 'User', $id);
        flash('success', __($new === 'Suspended' ? 'admin.suspended' : 'admin.activated', ['name' => $u['FullName']]));
        $this->back();
    }

    public function createSupervisor(): void
    {
        $_POST['email'] = strtolower(trim($_POST['email'] ?? ''));
        $d = $this->validate([
            'full_name'  => 'required|min:3|max:150',
            'email'      => 'required|email|max:190|unique:users,Email',
            'department' => 'required|max:150',
            'max_load'   => 'required|integer|between:1,200',
            'password'   => 'required|password',
        ]);
        $uid = Database::transaction(function () use ($d) {
            $uid = \User::insert(['FullName' => $d['full_name'], 'Email' => $d['email'], 'PasswordHash' => password_hash($d['password'], PASSWORD_BCRYPT), 'Role' => 'Supervisor']);
            \AcademicSupervisor::insert(['UserID' => $uid, 'Department' => $d['department'], 'MaxStudentLoad' => (int) $d['max_load']]);
            return $uid;
        });
        \AuditService::log('SUPERVISOR_CREATED', 'User', $uid);
        flash('success', __('admin.supervisor_created'));
        $this->redirect('/admin/users?role=Supervisor');
    }
}
