# Implementation Plan: BHC and RHU Structure

## What I'm Creating

1. **BHC Controllers** - All existing controllers adapted with HasRoleContext trait
2. **RHU Controllers** - Copies of BHC controllers (same functionality)
3. **Views** - Copied to `resources/views/bhc/` and `resources/views/rhu/`
4. **Routes** - Updated with role-based groups + backward compatibility

## Controllers to Create

### BHC Controllers:
- ReportsController
- InventoryController  
- ScheduleController
- ServicesController
- PersonnelController
- EventController
- CalendarController
- UserRequestController
- AccountController
- NotificationController

### RHU Controllers:
- Same as BHC (copies with namespace changed)

## Key Changes:
1. Use `HasRoleContext` trait
2. Replace `view('pages.xxx')` with `$this->view('xxx')`
3. Replace `session('user')` logic with `$this->getBarangayId()`, `$this->getUserId()`, etc.
4. Fix bugs (like `$this->barangayId` in ReportsController)

## Routes Strategy:
- Keep existing routes for backward compatibility
- Add new role-based routes with `/bhc/` and `/rhu/` prefixes
- Use role middleware to protect routes

