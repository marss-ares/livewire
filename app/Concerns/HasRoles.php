<?php

namespace App\Concerns;

use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasRoles
{
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function assignRole(Role|string $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        $this->update(['role_id' => $role->id]);
    }

    public function revokeRole(): void
    {
        $this->update(['role_id' => null]);
    }

    public function hasRole(string $slug): bool
    {
        return $this->role && $this->role->slug === $slug;
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->role && in_array($this->role->slug, $slugs);
    }

    public function hasPermission(string $slug): bool
    {
        return $this->role && $this->role->hasPermission($slug);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        if (!$this->role) {
            return false;
        }

        foreach ($slugs as $slug) {
            if ($this->role->hasPermission($slug)) {
                return true;
            }
        }

        return false;
    }
}
