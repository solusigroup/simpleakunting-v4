---
description: Comprehensive Testing Checklist for SimpleAkunting V4
---

# SimpleAkunting V4 - Testing Checklist

## 1. Authentication & Setup
- [ ] Login page loads correctly
- [ ] Login with valid credentials works
- [ ] Logout functionality works
- [ ] Setup Wizard accessible and functional
- [ ] Company settings can be updated

## 2. Dashboard
- [ ] Dashboard loads without errors
- [ ] All widgets display correct data
- [ ] Charts render properly
- [ ] Quick action buttons work

## 3. Master Data - Chart of Accounts
- [ ] List view displays all accounts
- [ ] Create new account works
- [ ] Edit account works
- [ ] Account details modal shows correct info
- [ ] Import CSV functionality works
- [ ] Download template works
- [ ] Search and filter work

## 4. Master Data - Contacts (Customers/Suppliers)
- [ ] List view displays all contacts
- [ ] Create new contact works
- [ ] Edit contact works
- [ ] Contact details modal shows correct info
- [ ] Import/Export CSV works
- [ ] Filter by type (Customer/Supplier) works

## 5. Master Data - Business Units
- [ ] List view displays all units
- [ ] Create new unit works
- [ ] Edit unit works
- [ ] Activation/deactivation works

## 6. Master Data - Inventory
- [ ] List view displays all inventory items
- [ ] Create new item works
- [ ] Edit item works
- [ ] Import/Export CSV works
- [ ] Stock tracking displays correctly

## 7. Master Data - Fixed Assets
- [ ] List view displays all assets
- [ ] Create new asset works
- [ ] Edit asset works
- [ ] Import/Export CSV works
- [ ] Depreciation calculation correct

## 8. Transactions - Sales
- [ ] Sales list displays all transactions
- [ ] Create new sales invoice works
- [ ] Invoice details modal shows correct info
- [ ] Journal entries created correctly
- [ ] Customer balance updated

## 9. Transactions - Purchases
- [ ] Purchase list displays all transactions
- [ ] Create new purchase invoice works
- [ ] Invoice details modal shows correct info
- [ ] Journal entries created correctly
- [ ] Supplier balance updated

## 10. Cash Transactions - Receipt
- [ ] Cash receipt form loads
- [ ] Create cash receipt works
- [ ] Journal entries created correctly
- [ ] Cash balance updated
- [ ] Validation prevents negative balance

## 11. Cash Transactions - Disbursement
- [ ] Cash disbursement form loads
- [ ] Create cash disbursement works
- [ ] Journal entries created correctly
- [ ] Cash balance updated
- [ ] Validation prevents overdraft

## 12. Journal Entries
- [ ] Journal list displays all entries
- [ ] Manual journal entry form works
- [ ] Debit/Credit balance validation works
- [ ] Journal details modal shows correct info
- [ ] Filter by date range works
- [ ] Filter by transaction type works

## 13. Closing & Adjustment
- [ ] Closing journal form loads
- [ ] Adjustment journal form loads
- [ ] Adjustment entry creation works
- [ ] Period closing works correctly

## 14. Budgets
- [ ] Budget list displays all budgets
- [ ] Create new budget works
- [ ] Edit budget works
- [ ] Delete budget works
- [ ] Budget comparison report works

## 15. Reports - Balance Sheet
- [ ] Report loads without errors
- [ ] Data displays correctly
- [ ] Date filter works
- [ ] PDF export works
- [ ] Comparative view works
- [ ] Print functionality works

## 16. Reports - Profit & Loss
- [ ] Report loads without errors
- [ ] Data displays correctly
- [ ] Date range filter works
- [ ] PDF export works
- [ ] Comparative view works
- [ ] Print functionality works

## 17. Reports - Trial Balance
- [ ] Report loads without errors
- [ ] Data displays correctly
- [ ] Date filter works
- [ ] Debit/Credit totals match
- [ ] Print functionality works

## 18. Reports - Ledger
- [ ] Report loads without errors
- [ ] Account selection works
- [ ] Transaction details display correctly
- [ ] Running balance calculates correctly
- [ ] Date range filter works
- [ ] Print functionality works

## 19. Reports - Cash Flow
- [ ] Report loads without errors
- [ ] Operating activities section correct
- [ ] Investing activities section correct
- [ ] Financing activities section correct
- [ ] PDF export works
- [ ] Print functionality works

## 20. Reports - Financial Analysis
- [ ] Report loads without errors
- [ ] Ratio calculations correct
- [ ] Charts display properly
- [ ] Period comparison works

## 21. Reports - Journal List
- [ ] Report loads without errors
- [ ] All journals display correctly
- [ ] Filter by date range works
- [ ] Filter by type works

## 22. Reports - Sales Report
- [ ] Report loads without errors
- [ ] Sales data displays correctly
- [ ] Date range filter works
- [ ] Customer filter works

## 23. Reports - Purchase Report
- [ ] Report loads without errors
- [ ] Purchase data displays correctly
- [ ] Date range filter works
- [ ] Supplier filter works

## 24. Reports - Equity Changes
- [ ] Report loads without errors
- [ ] Equity movements display correctly
- [ ] PDF export works
- [ ] Print functionality works

## 25. User Management (Admin only)
- [ ] User list displays all users
- [ ] Create new user works
- [ ] Edit user works
- [ ] Role assignment works
- [ ] User activation/deactivation works

## 26. Audit Trail (Admin only)
- [ ] Audit log list displays
- [ ] Filter by user works
- [ ] Filter by action works
- [ ] Filter by date works
- [ ] Audit details show correctly

## 27. UI/UX
- [ ] Dark mode toggle works
- [ ] Responsive design on mobile
- [ ] Navigation menu works
- [ ] Breadcrumbs display correctly
- [ ] Toast notifications appear
- [ ] Loading states display
- [ ] Error messages are clear

## 28. Performance
- [ ] Page load times acceptable
- [ ] No console errors
- [ ] Database queries optimized
- [ ] Large datasets load efficiently

## 29. Security
- [ ] Authentication required for all routes
- [ ] Role-based access control works
- [ ] CSRF protection active
- [ ] SQL injection prevention
- [ ] XSS prevention

## 30. Data Integrity
- [ ] Double-entry bookkeeping maintained
- [ ] Account balances calculate correctly
- [ ] No orphaned records
- [ ] Referential integrity maintained
