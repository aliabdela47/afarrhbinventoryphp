# Branch Structure

## copilot/init-afarrhb (Bootstrap 5 Version)
This branch contains the complete AfarRHB Inventory application with:
- Bootstrap 5 for UI styling
- Alpine.js for interactivity
- SweetAlert2 for notifications
- Bootstrap Icons

## Required: init_afarrhb_tailwind (Tailwind CSS Version)
To create the Tailwind variant, the following files would need to be modified while keeping all backend logic identical:

### Files to Modify for Tailwind Version:
1. **includes/header.php** - Replace Bootstrap CSS CDN with Tailwind Play CDN + Flowbite
2. **includes/footer.php** - Update any Bootstrap-specific JavaScript
3. **includes/sidebar.php** - Update classes to Tailwind equivalents
4. **All module pages** - Replace Bootstrap classes with Tailwind classes

### CSS Class Mapping (Bootstrap 5 → Tailwind):
- `.btn.btn-primary` → `bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded`
- `.card` → `bg-white shadow rounded-lg`
- `.table.table-striped` → `min-w-full divide-y divide-gray-200`
- `.badge.bg-success` → `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800`
- `.alert.alert-info` → `bg-blue-50 border-l-4 border-blue-400 p-4`
- `.form-control` → `mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500`

### Backend Files (No Changes Required):
- config.php
- includes/db.php
- includes/auth.php
- includes/audit.php
- includes/helpers.php
- init.sql
- lang/en.php
- lang/am.php
- All module business logic

The backend logic, database schema, authentication, audit logging, and all business logic would remain identical between both branches.
