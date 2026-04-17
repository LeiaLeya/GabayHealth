# Role-Based Access Control Structure

## Overview

This document explains the recommended structure for implementing role-based access control (RBAC) in your Laravel application.

## Roles

1. **BHC (Barangay Health Center)** - `barangay` role
2. **RHU (Rural Health Unit)** - `rhu` role  
3. **Health Workers** - `health-worker` role
4. **Admin** - `admin` role

## Recommended Structure

### ❌ DON'T Do This:
- ❌ Rename files with suffixes like `ReportsControllerBHC.php`
- ❌ Duplicate entire folders and copy files
- ❌ Create separate route files for each role

### ✅ DO This Instead:

#### 1. Controller Organization

```
app/Http/Controllers/
├── BHC/                    # Barangay Health Center controllers
│   ├── ReportsController.php
│   ├── InventoryController.php
│   ├── DashboardController.php
│   └── ...
├── RHU/                    # Rural Health Unit controllers
│   ├── ReportsController.php
│   ├── ScheduleController.php
│   ├── BHCController.php   # RHU manages BHCs
│   └── ...
├── HealthWorker/           # Health Worker controllers
│   ├── ReportsController.php
│   └── ...
├── Admin/                  # Admin controllers
│   └── ...
└── Traits/
    └── HasRoleContext.php  # Shared logic trait
```

**Benefits:**
- Clear namespace separation
- No file duplication
- Easy to find role-specific code
- Can share common logic via traits

#### 2. View Organization

```
resources/views/
├── bhc/                    # BHC-specific views
│   ├── reports/
│   │   ├── index.blade.php
│   │   ├── verify.blade.php
│   │   └── rejected.blade.php
│   ├── inventory/
│   └── ...
├── rhu/                    # RHU-specific views
│   ├── reports/
│   │   └── index.blade.php  # Different from BHC reports
│   ├── schedules/
│   └── ...
├── health-worker/          # Health Worker views
│   └── reports/
├── admin/                  # Admin views (already exists)
└── pages/                  # Shared/common views
    └── ...
```

**Benefits:**
- Role-specific views can have different layouts/features
- Can still share common components
- Easy to customize per role

#### 3. Route Organization

Use route groups with role middleware:

```php
// BHC Routes
Route::middleware(['auth.check', 'role:barangay,bhc'])
    ->prefix('bhc')
    ->name('bhc.')
    ->group(function () {
        Route::get('/dashboard', [BHC\DashboardController::class, 'index']);
        Route::get('/reports', [BHC\ReportsController::class, 'index']);
        // ...
    });

// RHU Routes
Route::middleware(['auth.check', 'role:rhu'])
    ->prefix('rhu')
    ->name('rhu.')
    ->group(function () {
        Route::get('/dashboard', [RHU\DashboardController::class, 'index']);
        Route::get('/schedules', [RHU\ScheduleController::class, 'index']);
        // ...
    });
```

**Benefits:**
- Automatic role checking
- Clean URL structure (`/bhc/reports`, `/rhu/schedules`)
- Easy to add new routes per role

## Migration Strategy

### Phase 1: Current State (BHC Only)
1. Keep existing controllers as-is
2. Start organizing new features by role

### Phase 2: Gradual Migration
1. When adding RHU features, create `app/Http/Controllers/RHU/` namespace
2. Create `resources/views/rhu/` folder
3. Add RHU routes with role middleware

### Phase 3: Refactor Existing (Optional)
1. Move BHC controllers to `BHC/` namespace
2. Move BHC views to `bhc/` folder
3. Update routes to use new structure

## Using the HasRoleContext Trait

All role-based controllers should use the `HasRoleContext` trait:

```php
namespace App\Http\Controllers\BHC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;

class ReportsController extends Controller
{
    use HasRoleContext;

    public function index()
    {
        // Get current user's role
        $role = $this->getUserRole(); // 'barangay'
        
        // Get barangay ID (handles role logic automatically)
        $barangayId = $this->getBarangayId();
        
        // Render view with automatic namespace
        return $this->view('reports.index', [
            'reports' => $reports
        ]);
        // This will look for: resources/views/bhc/reports/index.blade.php
    }
}
```

## Example: Same Feature, Different Roles

### BHC Reports
- **Controller:** `App\Http\Controllers\BHC\ReportsController`
- **View:** `resources/views/bhc/reports/index.blade.php`
- **Route:** `/bhc/reports`
- **Logic:** Shows reports for this specific BHC only

### RHU Reports
- **Controller:** `App\Http\Controllers\RHU\ReportsController`
- **View:** `resources/views/rhu/reports/index.blade.php`
- **Route:** `/rhu/reports`
- **Logic:** Shows aggregated reports from all BHCs under this RHU

Same feature name, different implementation per role!

## Benefits of This Approach

1. ✅ **No Code Duplication** - Share common logic via traits
2. ✅ **Clear Organization** - Easy to find role-specific code
3. ✅ **Scalable** - Easy to add new roles or features
4. ✅ **Maintainable** - Changes to one role don't affect others
5. ✅ **Laravel Best Practices** - Uses namespaces, middleware, route groups
6. ✅ **Flexible** - Can still share views/components when needed

## Next Steps

1. Review the example files:
   - `routes/web-role-based.php.example`
   - `app/Http/Controllers/BHC/ReportsController.php.example`
   - `app/Http/Controllers/RHU/ReportsController.php.example`

2. Start with new features using this structure

3. Gradually migrate existing code when convenient

4. Use the `HasRoleContext` trait in all role-based controllers

