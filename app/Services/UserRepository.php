<?php

namespace App\Services;

use App\Services\Concerns\JsonStore;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Pream's note for the team:
 * JSON-backed user store. Same pattern as the other repos so when we move to
 * SQLite the migration is trivial — single class to swap.
 *
 * Roles → Permissions (intentionally coarse; finer-grained later if needed):
 *   owner      → users.manage, posts.manage, timetables.manage, passes.manage, translations.edit
 *   editor     → posts.manage, timetables.manage, passes.manage, translations.edit
 *   translator → translations.edit                          (only the per-locale fields)
 *
 * Bootstrap: on first read, if no users.json exists, seed an owner from
 * .env (ADMIN_USERNAME / ADMIN_PASSWORD) so existing logins don't break.
 */
class UserRepository
{
    use JsonStore;

    public const ROLES = [
        'owner'      => ['label' => 'Owner',      'color' => 'bg-amber-100 text-amber-800'],
        'editor'     => ['label' => 'Editor',     'color' => 'bg-blue-100 text-blue-800'],
        'translator' => ['label' => 'Translator', 'color' => 'bg-purple-100 text-purple-800'],
    ];

    public const PERMISSIONS = [
        'owner' => [
            'users.manage', 'posts.manage', 'timetables.manage',
            'passes.manage', 'translations.edit',
        ],
        'editor' => [
            'posts.manage', 'timetables.manage', 'passes.manage', 'translations.edit',
        ],
        'translator' => [
            'translations.edit',
        ],
    ];

    protected string $jsonPath;

    public function __construct()
    {
        $this->jsonPath = storage_path('app/users.json');
        $this->seedIfMissing();
    }

    public function all(): array
    {
        $users = $this->load();
        usort($users, fn ($a, $b) => strcmp($a['username'] ?? '', $b['username'] ?? ''));
        return $users;
    }

    public function find(string $id): ?array
    {
        foreach ($this->load() as $u) {
            if (($u['id'] ?? null) === $id) return $u;
        }
        return null;
    }

    public function findByUsername(string $username): ?array
    {
        foreach ($this->load() as $u) {
            if (strcasecmp($u['username'] ?? '', $username) === 0) return $u;
        }
        return null;
    }

    /** Verify a username + plaintext password. Returns the user or null. */
    public function verifyLogin(string $username, string $password): ?array
    {
        $u = $this->findByUsername($username);
        if (!$u || empty($u['is_active']) || empty($u['password_hash'])) return null;
        if (!Hash::check($password, $u['password_hash'])) return null;
        return $u;
    }

    public function create(array $data): array
    {
        $users = $this->load();
        $row = [
            'id' => (string) Str::uuid(),
            'username' => $data['username'],
            'name' => $data['name'] ?? $data['username'],
            'email' => $data['email'] ?? '',
            'role' => $data['role'] ?? 'editor',
            'password_hash' => Hash::make($data['password']),
            'is_active' => $data['is_active'] ?? true,
            'created_at' => date('Y-m-d H:i:s'),
            'last_login_at' => null,
        ];
        $users[] = $row;
        $this->save($users);
        return $row;
    }

    public function update(string $id, array $data): ?array
    {
        $users = $this->load();
        foreach ($users as $i => $u) {
            if (($u['id'] ?? null) !== $id) continue;
            // Re-hash if a password was supplied; otherwise keep the existing hash.
            if (!empty($data['password'])) {
                $data['password_hash'] = Hash::make($data['password']);
            }
            unset($data['password']);
            // Username is immutable for safety (admins should delete + recreate to rename).
            unset($data['username']);
            $users[$i] = array_merge($u, $data);
            $this->save($users);
            return $users[$i];
        }
        return null;
    }

    public function delete(string $id): bool
    {
        $users = $this->load();
        $kept = array_values(array_filter($users, fn ($u) => ($u['id'] ?? null) !== $id));
        if (count($kept) === count($users)) return false;
        $this->save($kept);
        return true;
    }

    public function recordLogin(string $id): void
    {
        $this->update($id, ['last_login_at' => date('Y-m-d H:i:s')]);
    }

    /** Returns the permissions array for a role. */
    public static function permissionsFor(string $role): array
    {
        return self::PERMISSIONS[$role] ?? [];
    }

    /** Quick check: does this user (array shape) have a given permission? */
    public static function userCan(?array $user, string $permission): bool
    {
        if (!$user) return false;
        return in_array($permission, self::permissionsFor($user['role'] ?? ''), true);
    }

    // ----- internals -----

    protected function load(): array
    {
        return $this->jsonRead($this->jsonPath);
    }

    protected function save(array $data): void
    {
        $this->jsonWrite($this->jsonPath, $data);
    }

    protected function seedIfMissing(): void
    {
        if (file_exists($this->jsonPath)) return;
        $username = config('app.admin_username', env('ADMIN_USERNAME', 'admin'));
        $password = config('app.admin_password', env('ADMIN_PASSWORD', 'admin123'));
        $this->save([[
            'id' => (string) Str::uuid(),
            'username' => $username,
            'name' => 'Owner',
            'email' => '',
            'role' => 'owner',
            'password_hash' => Hash::make($password),
            'is_active' => true,
            'created_at' => date('Y-m-d H:i:s'),
            'last_login_at' => null,
        ]]);
    }
}
