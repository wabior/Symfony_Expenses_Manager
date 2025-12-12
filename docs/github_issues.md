# GitHub Issues - Lista zadań do implementacji

Ten plik zawiera listę wszystkich zadań (issues) do zaimplementowania w aplikacji Symfony Expenses Manager, pogrupowanych według milestone'ów. Każde zadanie zawiera szczegółowy opis, wymagania, kryteria akceptacji i uwagi techniczne.

## Spis treści

1. [Secure Multi-User Expense Management](#secure-multi-user-expense-management)
2. [Complete Expense CRUD Operations](#complete-expense-crud-operations)
3. [Recurring Expenses System Operational](#recurring-expenses-system-operational)
4. [Polished User Interface & Experience](#polished-user-interface--experience)
5. [Professional Reporting & Analytics](#professional-reporting--analytics)
6. [Advanced Features & Future-Proofing](#advanced-features--future-proofing)

---

## Secure Multi-User Expense Management

### User-Expense Relationship & Security

**Status**: Nie rozpoczęty
**Priorytet**: Krytyczny (Bezpieczeństwo)
**Assignee**: TBD

#### Description
Implement user-specific expense management by adding user relationship to expenses and enforcing row-level security. This is a critical security and functionality requirement - currently all users can see all expenses in the system.

#### Requirements:
- Add `user_id` foreign key to `expense` table
- Update Expense entity with User relationship
- Modify all expense queries to filter by current user
- Update expense controllers to assign current user when creating expenses
- Add data migration for existing expenses (assign to first admin user or require reassignment)
- Update fixtures to create expenses for specific users
- Ensure proper cascading deletes

#### Acceptance Criteria:
- Users can only see their own expenses
- New expenses are automatically assigned to current user
- Existing expenses are properly migrated
- All expense operations respect user ownership

#### Technical Notes:
- Modify Expense entity, ExpenseService, ExpenseController
- Add database migration
- Update expense repository methods
- Test with multiple users

#### Test Cases:
- User A cannot see expenses created by User B
- New expenses are automatically assigned to logged-in user
- Migration correctly assigns existing expenses to admin user

---

### Category Management Enhancement

**Status**: Nie rozpoczęty
**Priorytet**: Wysoki
**Assignee**: TBD

#### Description
Add edit and delete functionality for expense categories to complete CRUD operations. Currently categories can only be created, but not modified or removed.

#### Requirements:
- Add edit category form and controller method
- Add delete category with validation (prevent deletion if category has expenses)
- Update category list to show edit/delete buttons
- Add confirmation dialogs for delete operations
- Update category service with edit/delete methods
- Add proper error handling and user feedback

#### Acceptance Criteria:
- Categories can be edited (name in both languages)
- Categories can be deleted only if no expenses use them
- Proper validation and error messages
- UI updates reflect changes immediately

#### Technical Notes:
- Modify CategoryController, CategoryService
- Add new routes for edit/delete
- Update category templates
- Consider category re-assignment when deleting

#### Test Cases:
- Edit category name updates both languages
- Cannot delete category with existing expenses
- Confirmation dialog appears before deletion

---

## Complete Expense CRUD Operations

### Expense CRUD Operations

**Status**: Nie rozpoczęty
**Priorytet**: Wysoki
**Assignee**: TBD

#### Description
Implement edit and delete functionality for individual expenses. Currently expenses can only be created and status updated, but not fully edited or removed.

#### Requirements:
- Add edit expense form with all fields (name, amount, date, category, payment status)
- Add delete expense with confirmation
- Update expense list to show edit/delete buttons for each expense
- Preserve recurring expense relationships when editing
- Add proper validation and error handling
- Update expense service with edit/delete methods

#### Acceptance Criteria:
- Expenses can be fully edited
- Expenses can be deleted with confirmation
- Form validation works correctly
- Changes reflect in expense list immediately
- Recurring expenses maintain their relationships

#### Technical Notes:
- Modify ExpenseController, ExpenseService
- Add new routes and templates
- Handle recurring expense logic during edits
- Add CSRF protection

#### Test Cases:
- Edit expense updates all fields correctly
- Delete expense removes it from database
- Confirmation required for deletion
- Form validation prevents invalid data

---

### Bulk Expense Operations

**Status**: Nie rozpoczęty
**Priorytet**: Wysoki
**Assignee**: TBD

#### Description
Add bulk operations for managing multiple expenses at once - bulk delete, bulk status update, and bulk category changes.

#### Requirements:
- Add checkboxes to expense table for selection
- Add bulk action toolbar (delete, change status, change category)
- Implement bulk delete with confirmation
- Implement bulk status updates (paid/unpaid)
- Implement bulk category reassignment
- Add progress indicators for large operations
- Proper error handling for failed operations

#### Acceptance Criteria:
- Multiple expenses can be selected and operated on
- Bulk operations complete successfully
- Proper confirmation dialogs for destructive actions
- Progress feedback for long-running operations
- Error handling for partial failures

#### Technical Notes:
- Modify expense index template
- Add JavaScript for selection handling
- Extend ExpenseService with bulk methods
- Consider batch processing for large datasets

#### Test Cases:
- Select multiple expenses and bulk delete
- Bulk status change updates all selected expenses
- Progress bar shows during long operations
- Error handling for failed bulk operations

---

## Recurring Expenses System Operational

### Recurring Expenses System

**Status**: Nie rozpoczęty
**Priorytet**: Wysoki
**Assignee**: TBD

#### Description
Implement recurring expenses functionality allowing users to create expenses that automatically repeat in future months.

#### Requirements:
- Add recurring frequency field to expense form (1, 2, 3, 6, 12 months)
- Add "Create Next Month" functionality to copy unpaid recurring expenses
- Add visual indicators for recurring expenses
- Implement parent-child relationship for recurring expenses
- Add database fields: recurring_frequency, parent_expense_id
- Update expense queries to handle recurring logic

#### Acceptance Criteria:
- Users can mark expenses as recurring
- "Create Next Month" copies appropriate recurring expenses
- Recurring expenses have visual indicators
- Recurring relationships are properly maintained
- Users can modify recurring settings

#### Technical Notes:
- Add database migration for new fields
- Update Expense entity with self-referencing relationship
- Modify ExpenseService, ExpenseRepository
- Update forms and templates
- Add proper validation

#### Test Cases:
- Create recurring expense with different frequencies
- "Create Next Month" copies only appropriate expenses
- Recurring expenses show visual indicators
- Edit recurring expense updates frequency correctly

---

## Polished User Interface & Experience

### Table Sorting & Language Switch

**Status**: Nie rozpoczęty
**Priorytet**: Średni
**Assignee**: TBD

#### Description
Enhance user experience with sortable expense table columns and English/Polish language switching functionality.

#### Requirements:
- Add clickable column headers for sorting (date, name, amount, category, status)
- Implement client-side and server-side sorting options
- Add language switcher in navigation
- Implement translation system with English/Polish locales
- Update all templates with translation keys
- Add language preference persistence

#### Acceptance Criteria:
- All table columns are sortable (ascending/descending)
- Language switcher works without page refresh
- All UI text is properly translated
- Language preference is saved per user
- RTL/LTR support if needed

#### Technical Notes:
- Add JavaScript for table sorting
- Implement Symfony translation system
- Update all templates with translatable strings
- Add language routes and controller
- Store language preference in session/user settings

#### Test Cases:
- Click column header sorts table
- Language switch changes all UI text
- Language preference persists across sessions
- All templates support translations

---

### Mobile UX & Dark Mode

**Status**: Nie rozpoczęty
**Priorytet**: Średni
**Assignee**: TBD

#### Description
Improve mobile responsiveness and add dark mode support for better user experience across devices.

#### Requirements:
- Optimize all templates for mobile devices
- Add dark mode toggle and theme switching
- Implement responsive design improvements
- Add mobile-specific navigation patterns
- Test and fix mobile-specific issues
- Add proper touch interactions

#### Acceptance Criteria:
- App works well on mobile devices
- Dark mode works across all pages
- Theme preference is saved
- Responsive design breakpoints work correctly
- Touch interactions are smooth

#### Technical Notes:
- Update Tailwind CSS configuration
- Add dark mode classes to all templates
- Implement theme switching JavaScript
- Test on various mobile devices
- Add mobile-specific CSS optimizations

#### Test Cases:
- App displays correctly on mobile devices
- Dark mode toggle works on all pages
- Theme preference persists across sessions
- Touch gestures work properly

---

## Professional Reporting & Analytics

### Basic Reporting & Dashboard

**Status**: Nie rozpoczęty
**Priorytet**: Średni
**Assignee**: TBD

#### Description
Implement basic financial reporting and dashboard with expense summaries, category breakdowns, and monthly trends.

#### Requirements:
- Add dashboard page with monthly summary widgets
- Add expense reports by category and month
- Add basic charts (pie chart for categories, bar chart for monthly totals)
- Add export to CSV functionality
- Create reports controller and templates
- Add navigation to reports section

#### Acceptance Criteria:
- Dashboard shows current month summary
- Category breakdown with percentages
- Monthly trend visualization
- CSV export works correctly
- Reports are user-specific (only their expenses)

#### Technical Notes:
- Add new controller for reports
- Use Chart.js or similar for visualizations
- Add CSV generation library
- Extend ExpenseService with reporting methods
- Add proper date filtering

#### Test Cases:
- Dashboard shows accurate monthly totals
- Charts display correct data
- CSV export contains all expense data
- Reports filter by current user only

---

## Advanced Features & Future-Proofing

### User Profile & Password Management

**Status**: Nie rozpoczęty
**Priorytet**: Niski
**Assignee**: TBD

#### Description
Implement user profile management and password reset functionality to complete user account features.

#### Requirements:
- Add user profile page (view/edit email, name if added)
- Implement password change functionality
- Add password reset via email flow
- Add email verification for new registrations
- Update user entity if needed for additional fields
- Add proper security measures (rate limiting, secure tokens)

#### Acceptance Criteria:
- Users can change their password securely
- Password reset emails are sent correctly
- Email verification works for new accounts
- Profile information can be updated
- Security measures prevent abuse

#### Technical Notes:
- Extend SecurityController
- Add SwiftMailer integration
- Implement secure token generation
- Add email templates
- Update user forms and validation

#### Test Cases:
- Password change works securely
- Password reset email is sent
- Email verification for new accounts
- Profile editing updates user data

---

### Advanced Search & Data Export

**Status**: Nie rozpoczęty
**Priorytet**: Niski
**Assignee**: TBD

#### Description
Add advanced search functionality and comprehensive data export options for better expense management.

#### Requirements:
- Add search form with multiple filters (date range, category, amount, status)
- Implement search in expense list
- Add export to PDF (with charts and summaries)
- Add export to Excel format
- Add print-friendly views
- Implement search result pagination

#### Acceptance Criteria:
- Advanced search works with multiple criteria
- Search results are properly filtered and paginated
- PDF export includes charts and formatting
- Excel export maintains data integrity
- Print views are optimized for printing

#### Technical Notes:
- Extend ExpenseRepository with search methods
- Add PDF generation library (TCPDF/DomPDF)
- Add Excel export library (PhpSpreadsheet)
- Update expense list with search form
- Add pagination support

#### Test Cases:
- Search with multiple filters returns correct results
- PDF export includes charts and proper formatting
- Excel export maintains data structure
- Pagination works with large result sets

---

## Instrukcje implementacji

### Kolejność wykonania:
1. **Secure Multi-User Expense Management** - bezpieczeństwo przede wszystkim
2. **Complete Expense CRUD Operations** - podstawowa funkcjonalność
3. **Recurring Expenses System Operational** - biznesowa wartość
4. **Polished User Interface & Experience** - UX/UI
5. **Professional Reporting & Analytics** - analityka
6. **Advanced Features & Future-Proofing** - dodatki

### Przy tworzeniu GitHub Issues:
- Użyj tytułów z tego dokumentu
- Skopiuj opis, wymagania i kryteria akceptacji
- Dodaj odpowiedni milestone
- Dodaj etykiety: priority, status, component
- Przypisz osobę odpowiedzialną jeśli znana

### Przy implementacji:
- Aktualizuj status w tym dokumencie
- Dodawaj test cases podczas developmentu
- Dokumentuj wszelkie zmiany w API lub bazie danych
- Aktualizuj dokumentację dla AI po zakończeniu zadań

## Status wszystkich zadań: Nie rozpoczęte

**Data ostatniej aktualizacji**: Grudzień 2025
