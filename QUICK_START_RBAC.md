# Quick Start: Role-Based Access Control

## What I've Set Up For You

✅ **RoleMiddleware** - Middleware to check user roles  
✅ **HasRoleContext Trait** - Helper methods for role-based controllers  
✅ **Example Structure** - Examples showing how to organize by role  
✅ **Documentation** - Complete guide in `ROLE_BASED_STRUCTURE.md`

## Your Current Situation

You're currently working on **BHC (Barangay Health Center)** features. Later you'll add:
- **RHU (Rural Health Unit)** features
- **Health Workers** features

## My Recommendation: DON'T Rename Files

Instead of:
- ❌ Renaming files to `ReportsControllerBHC.php`
- ❌ Creating separate folders and copying files

Do this:
- ✅ Use **namespaces** for controllers (`App\Http\Controllers\BHC\`)
- ✅ Use **folders** for views (`resources/views/bhc/`)
- ✅ Use **route groups** with role middleware

## How to Use Right Now (BHC)

### Option 1: Keep Current Structure (Easiest)
Your current code works fine! When you're ready to add RHU features, use the new structure.

### Option 2: Start Organizing Now (Recommended)
1. Create folder: `app/Http/Controllers/BHC/`
2. Move/create BHC controllers there
3. Create folder: `resources/views/bhc/`
4. Move/create BHC views there
5. Update routes to use role middleware

## Example: Adding a New BHC Feature

### 1. Create Controller
```php
// app/Http/Controllers/BHC/InventoryController.php
namespace App\Http\Controllers\BHC;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;

class InventoryController extends Controller
{
    use HasRoleContext;

    public function index()
    {
        $barangayId = $this->getBarangayId();
        
        return $this->view('inventory.index', [
            'items' => []
        ]);
    }
}
```

### 2. Create View
```blade
{{-- resources/views/bhc/inventory/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>BHC Inventory</h1>
    {{-- Your BHC-specific inventory view --}}
@endsection
```

### 3. Add Route
```php
// routes/web.php
Route::middleware(['auth.check', 'role:barangay,bhc'])
    ->prefix('bhc')
    ->name('bhc.')
    ->group(function () {
        Route::get('/inventory', [BHC\InventoryController::class, 'index'])
            ->name('inventory.index');
    });
```

## When Adding RHU Features Later

### 1. Create RHU Controller
```php
// app/Http/Controllers/RHU/ScheduleController.php
namespace App\Http\Controllers\RHU;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\HasRoleContext;

class ScheduleController extends Controller
{
    use HasRoleContext;

    public function index()
    {
        // RHU-specific logic
        return $this->view('schedules.index', [
            'schedules' => []
        ]);
    }
}
```

### 2. Create RHU View
```blade
{{-- resources/views/rhu/schedules/index.blade.php --}}
@extends('layouts.app')

@section('content')
    <h1>RHU Schedules</h1>
    {{-- Your RHU-specific schedule view --}}
@endsection
```

### 3. Add RHU Route
```php
Route::middleware(['auth.check', 'role:rhu'])
    ->prefix('rhu')
    ->name('rhu.')
    ->group(function () {
        Route::get('/schedules', [RHU\ScheduleController::class, 'index'])
            ->name('schedules.index');
    });
```

## Key Benefits

1. **No Duplication** - Same feature name, different implementation per role
2. **Clear URLs** - `/bhc/reports` vs `/rhu/reports`
3. **Automatic Role Checking** - Middleware handles access control
4. **Easy to Maintain** - Changes to BHC don't affect RHU

## Files Created

- `app/Http/Middleware/RoleMiddleware.php` - Role checking middleware
- `app/Http/Controllers/Traits/HasRoleContext.php` - Helper trait
- `routes/web-role-based.php.example` - Example route structure
- `app/Http/Controllers/BHC/ReportsController.php.example` - Example BHC controller
- `app/Http/Controllers/RHU/ReportsController.php.example` - Example RHU controller
- `ROLE_BASED_STRUCTURE.md` - Complete documentation

## Next Steps

1. **Review** the example files (`.example` files)
2. **Read** `ROLE_BASED_STRUCTURE.md` for full details
3. **Start small** - Use this structure for new features
4. **Migrate gradually** - Move existing code when convenient

## Questions?

The structure is flexible - you can:
- Keep existing code as-is
- Gradually migrate to new structure
- Mix both approaches during transition

The key is: **Don't duplicate code, use namespaces and folders instead!**

