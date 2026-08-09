<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class Users extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $db = \Config\Database::connect();

        // Search and pagination
        $search = trim((string)$this->request->getGet('q'));
        $page = max(1, (int)$this->request->getGet('page'));
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $builder = $db->table('users')
            ->select("users.id, users.username, ai.secret as email, users.active, users.created_at, users.updated_at")
            ->select('GROUP_CONCAT(DISTINCT agu.group) as user_groups')
            ->join('auth_identities ai', "ai.user_id = users.id AND ai.type = 'email_password'", 'left')
            ->join('auth_groups_users agu', 'agu.user_id = users.id', 'left')
            ->groupBy('users.id')
            ->orderBy('users.id', 'DESC');

        if ($search !== '') {
            $builder->groupStart()
                ->like('users.username', $search)
                ->orLike('ai.secret', $search)
                ->groupEnd();
        }

        $total = $db->table('users')
            ->select('COUNT(DISTINCT users.id)')
            ->join('auth_identities ai', "ai.user_id = users.id AND ai.type = 'email_password'", 'left')
            ->when($search !== '', function ($q) use ($search) {
                $q->groupStart()
                    ->like('users.username', $search)
                    ->orLike('ai.secret', $search)
                    ->groupEnd();
            })
            ->get()->getRow()->{'COUNT(DISTINCT users.id)'};

        $users = $builder->limit($perPage, $offset)->get()->getResultArray();

        // Calculate storage per user (sms bytes + loot count estimate)
        $userIds = array_column($users, 'id');
        $storage = [];
        if (!empty($userIds)) {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));
            // SMS body bytes
            $rows = $db->query("
                SELECT i.user_id, SUM(LENGTH(s.sms_body)) as total_bytes, COUNT(*) as cnt
                FROM tbl_Sms s
                INNER JOIN auth_identities i ON i.secret = SHA2(s.sms_owner, 256)
                WHERE i.user_id IN ($placeholders)
                GROUP BY i.user_id
            ", $userIds)->getResultArray();
            foreach ($rows as $r) $storage[$r['user_id']] = ($storage[$r['user_id']] ?? 0) + (int)$r['total_bytes'];

            // Loot rows (no file size stored, estimate ~1KB per upload)
            $rows = $db->query("
                SELECT i.user_id, COUNT(*) as cnt
                FROM tbl_Loot l
                INNER JOIN auth_identities i ON i.secret = SHA2(l.loot_Owner, 256)
                WHERE i.user_id IN ($placeholders)
                GROUP BY i.user_id
            ", $userIds)->getResultArray();
            foreach ($rows as $r) $storage[$r['user_id']] = ($storage[$r['user_id']] ?? 0) + (int)$r['cnt'] * 1024;
        }

        foreach ($users as &$u) {
            $u['groups'] = $u['user_groups'] ?: '—';
            $u['storage_bytes'] = $storage[$u['id']] ?? 0;
            $u['storage_human'] = $this->humanSize($u['storage_bytes']);
        }

        $data = [
            'bg_color' => '#B1B8ED',
            'users' => $users,
            'search' => $search,
            'page' => $page,
            'per_page' => $perPage,
            'total' => $total,
            'total_pages' => ceil($total / $perPage),
        ];

        return view('Admin/Users/index', $data);
    }

    public function toggle()
    {
        $id = (int)$this->request->getPost('user_id');
        $active = (bool)$this->request->getPost('active');

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $id)->get()->getRow();
        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
        }

        // Prevent deactivating yourself
        if ($id === auth()->id()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cannot change your own status']);
        }

        $db->table('users')->where('id', $id)->update(['active' => $active ? 1 : 0]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $active ? 'User activated' : 'User deactivated',
        ]);
    }

    public function changeGroup()
    {
        $id = (int)$this->request->getPost('user_id');
        $group = trim((string)$this->request->getPost('group')); // 'superadmin' or 'default' or 'admin'

        $allowedGroups = ['user', 'admin', 'superadmin'];
        if (!in_array($group, $allowedGroups, true)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid group']);
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $id)->get()->getRow();
        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
        }

        if ($id === auth()->id()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cannot change your own group']);
        }

        // Remove from all groups, add to new group (Shield stores group name in `group` column)
        $db->table('auth_groups_users')->where('user_id', $id)->delete();
        $db->table('auth_groups_users')->insert([
            'user_id' => $id,
            'group' => $group,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => "User group changed to $group",
        ]);
    }

    public function delete()
    {
        $id = (int)$this->request->getPost('user_id');

        if ($id === auth()->id()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cannot delete yourself']);
        }

        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $id)->get()->getRow();
        if (!$user) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'User not found']);
        }

        // Delete user data
        $db->table('auth_groups_users')->where('user_id', $id)->delete();
        $db->table('auth_identities')->where('user_id', $id)->delete();
        $db->table('auth_permissions_users')->where('user_id', $id)->delete();
        $db->table('auth_logins')->where('user_id', $id)->delete();
        $db->table('auth_remember_tokens')->where('user_id', $id)->delete();
        $db->table('auth_token_logins')->where('user_id', $id)->delete();
        $db->table('users')->where('id', $id)->delete();
        // Also clean up their data tables
        foreach (['tbl_Loot', 'tbl_Sms', 'tbl_Analyzed_Transactions', 'tbl_Processing_Jobs', 'tbl_User_Settings'] as $t) {
            if ($db->tableExists($t)) {
                $db->table($t)->where('user_id', $id)->delete();
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'User deleted',
        ]);
    }

    private function humanSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}