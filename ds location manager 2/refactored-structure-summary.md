# DS Location Manager V2 - Refactored Structure

## New File Organization

```
/wp-content/plugins/ds_location_manager_v2/
├── ds_location_manager_v2.php          (UPDATED - Main plugin file)
├── includes/
│   ├── meta-boxes.php                  (NEW - Meta box functionality)
│   └── admin-customizations.php        (NEW - Admin UI customizations)
├── templates/
│   └── single-location.php             (NEW - Location template)
├── assets/
│   ├── location-template.css           (NEW - Template CSS)
│   ├── location-template.js            (NEW - Map JavaScript)
│   └── location-frontend.css           (existing)
├── location-template-functions.php     (existing)
├── patterns.php                        (existing)
└── rest.php                            (existing)
```

## What Changed

### Main Plugin File (`ds_location_manager_v2.php`)
**Kept:**
- Core CPT and taxonomy registration
- Role creation
- Access control methods
- Template loader methods (NEW)
- Term mapping functions

**Removed to separate files:**
- All meta box code → `includes/meta-boxes.php`
- All admin UI code → `includes/admin-customizations.php`

### New Files Created

1. **`includes/meta-boxes.php`**
   - `DS_Location_Meta_Boxes` class
   - Logo upload with media library
   - All field rendering and saving
   - Stats box

2. **`includes/admin-customizations.php`**
   - `DS_Location_Admin_Customizations` class
   - Dashboard customizations
   - Menu customizations
   - Admin bar modifications
   - User profile fields

3. **Template files** (from previous artifacts)
   - `templates/single-location.php`
   - `assets/location-template.css`
   - `assets/location-template.js`

## Installation Steps

1. **Backup your existing plugin first!**

2. **Replace main file:**
   - Replace `ds_location_manager_v2.php` with the refactored version

3. **Create new directory:**
   ```
   mkdir /wp-content/plugins/ds_location_manager_v2/includes
   ```

4. **Add new files:**
   - Create `includes/meta-boxes.php` (copy from artifact)
   - Create `includes/admin-customizations.php` (copy from artifact)

5. **Add template files** (from implementation guide):
   - Create `templates/single-location.php`
   - Create `assets/location-template.css`
   - Create `assets/location-template.js`

6. **Deactivate and reactivate plugin** to ensure everything loads properly

## Benefits of This Structure

✅ **Easier to maintain** - Each file has a single responsibility
✅ **Better for collaboration** - Multiple developers can work on different files
✅ **Easier to debug** - Issues are isolated to specific files
✅ **Follows WordPress standards** - Proper plugin architecture
✅ **Scalable** - Easy to add new features in new files
✅ **No more artifact size limits** - Files are properly sized

## What Still Works

- All existing functionality remains unchanged
- Location managers see the same interface
- Meta boxes work identically
- Access control unchanged
- REST API unchanged
- Block patterns unchanged

## New Meta Fields Added

All in `includes/meta-boxes.php`:
- **Logo** - Image upload via media library
- **City** - Text field with auto-extraction from address
- **YYCD Program Description** - Large textarea for program details
- **Latitude/Longitude** - For map display

## Testing Checklist

After installation:
- [ ] Plugin activates without errors
- [ ] Create/edit location works
- [ ] Logo upload works
- [ ] All meta fields save properly
- [ ] Location manager dashboard displays
- [ ] Access control works for location managers
- [ ] Front-end template loads (if template files created)
- [ ] Map displays with coordinates

## Next Steps

1. Install the refactored files
2. Test existing functionality
3. Add the template files for front-end display
4. Style the template in your child theme
5. Consider the TODO items:
   - Location editor wizard/UX improvements
   - Hours/schedule system
   - Map enhancements (geocoding)
   - Gallery support
   - SEO schema markup