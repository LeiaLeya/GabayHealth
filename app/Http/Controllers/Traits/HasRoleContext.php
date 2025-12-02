<?php

namespace App\Http\Controllers\Traits;

use App\Services\FirebaseService;

trait HasRoleContext
{
    /**
     * Get the current user's role
     */
    protected function getUserRole(): ?string
    {
        $user = session('user');
        return $user['role'] ?? null;
    }

    /**
     * Get the current user's ID
     */
    protected function getUserId(): ?string
    {
        $user = session('user');
        return $user['id'] ?? null;
    }

    /**
     * Get the barangay ID based on role
     * For barangay users, it's their own ID
     * For other roles, it's stored in barangayId
     */
    protected function getBarangayId(): ?string
    {
        $user = session('user');
        $role = $user['role'] ?? null;
        
        if ($role === 'barangay') {
            return $user['id'] ?? null;
        }
        
        return $user['barangayId'] ?? null;
    }

    /**
     * Get the collection name based on role
     */
    protected function getCollectionNameByRole(?string $role = null): string
    {
        $role = $role ?? $this->getUserRole();
        
        switch ($role) {
            case 'barangay':
                return 'barangay';
            case 'rhu':
                return 'rhu';
            case 'health-worker':
                return 'health-worker';
            case 'admin':
                return 'admin';
            default:
                return 'barangay';
        }
    }

    /**
     * Get the view namespace based on role
     */
    protected function getViewNamespaceByRole(?string $role = null): string
    {
        $role = $role ?? $this->getUserRole();
        
        switch ($role) {
            case 'barangay':
                return 'bhc';
            case 'rhu':
                return 'rhu';
            case 'health-worker':
                return 'health-worker';
            case 'admin':
                return 'admin';
            default:
                return 'bhc';
        }
    }

    /**
     * Render a view with role-specific namespace
     */
    protected function view(string $view, array $data = [])
    {
        $namespace = $this->getViewNamespaceByRole();
        $viewPath = "{$namespace}.{$view}";
        
        // Fallback to pages namespace if role-specific view doesn't exist
        if (!view()->exists($viewPath)) {
            $viewPath = "pages.{$view}";
        }
        
        return view($viewPath, $data);
    }
}

