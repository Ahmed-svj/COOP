<?php
namespace Controllers\Company;

use Database;

class ApplicationController extends Base
{
    /** UC-09: review applicants and CVs. */
    public function index(): void
    {
        $c = $this->company();
        $oppId = (int) ($_GET['opportunity'] ?? 0);
        $status = in_array($_GET['status'] ?? '', ['Pending', 'Accepted', 'Rejected', 'Withdrawn'], true) ? $_GET['status'] : 'Pending';
        $params = [$c['CompanyID'], $status];
        $extra = '';
        if ($oppId) {
            $extra = ' AND o.OpportunityID = ?';
            $params[] = $oppId;
        }
        $items = Database::all(
            "SELECT a.*, o.Title, o.Capacity, u.FullName, u.Email, u.Phone, s.Major, s.GPA, s.InstitutionalID, s.StudentID, s.CVFilePath,
                    (SELECT PlacementID FROM training_placements p WHERE p.OpportunityID = o.OpportunityID) AS PlacementID,
                    (SELECT COUNT(*) FROM applications x WHERE x.OpportunityID = o.OpportunityID AND x.Status = 'Accepted') AS AcceptedCount
               FROM applications a JOIN coop_opportunities o ON o.OpportunityID = a.OpportunityID
               JOIN students s ON s.StudentID = a.StudentID JOIN users u ON u.UserID = s.UserID
              WHERE o.CompanyID = ? AND a.Status = ?$extra ORDER BY a.ApplicationID DESC", $params);
        $opps = Database::all('SELECT OpportunityID, Title FROM coop_opportunities WHERE CompanyID = ? ORDER BY Title', [$c['CompanyID']]);
        $this->view('company/applicants', compact('items', 'opps', 'oppId', 'status'));
    }

    /** UC-10: accept (transaction, capacity-safe). */
    public function accept(int $id): void
    {
        $c = $this->requireApproved();
        $start = $this->input('start_date') ?: null;
        $end = $this->input('end_date') ?: null;
        foreach ([$start, $end] as $d) {
            if ($d !== null && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $d)) {
                flash('error', __('v.date'));
                $this->back();
            }
        }
        $err = \PlacementService::accept($id, (int) $c['CompanyID'], $start, $end);
        flash($err ? 'error' : 'success', $err ?? __('app.accepted'));
        $this->back();
    }

    public function reject(int $id): void
    {
        $c = $this->requireApproved();
        $reason = mb_substr(trim((string) ($_POST['reason'] ?? '')), 0, 1000);
        if ($reason === '') {
            flash('error', __('app.reason_required'));
            $this->back();
        }
        $err = \PlacementService::reject($id, (int) $c['CompanyID'], $reason);
        flash($err ? 'error' : 'success', $err ?? __('app.rejected'));
        $this->back();
    }
}
