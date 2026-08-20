<?php

return [
    'title'                     => 'Operational Expenses & Cost Centers',
    'expenses_breakdown'        => 'Expense details, landed costs, and services',
    'expense_item'              => 'Expense Title / Item',
    'amount'                    => 'Amount (EGP)',
    'who_pays'                  => 'Who Pays / Settles the Expense?',
    'allocation_method'         => 'Cost Allocation Method:',
    'notes'                     => 'Expense Notes',
    'add_expense'               => 'Add Expense',
    'custom_expense'            => 'Custom Expense',

    // Who pays options
    'paid_by_customer'          => 'Billed to Customer Invoice',
    'paid_by_supplier'          => 'Added to Supplier Payable',
    'paid_by_treasury_cash'     => 'Paid in Cash from Drawer (Disbursement)',
    'paid_by_treasury_instapay' => 'Paid via InstaPay from Account',
    'paid_by_treasury_e_wallet' => 'Paid via Smart E-Wallet',

    // Allocation methods
    'alloc_by_quantity'         => 'By Weight / Quantity',
    'alloc_by_value'            => 'By Item Value / Price',
    'alloc_equal'               => 'Equally across all items',

    // Presets
    'preset_shipping'           => 'Shipping & Delivery',
    'preset_loading'            => 'Loading & Labor',
    'preset_packaging'          => 'Boxes & Packaging',
    'preset_customs'            => 'Customs & Port Fees',
    'preset_tip'                => 'Driver / Courier Tip',

    // Landed cost metrics
    'base_cost'                 => 'Base Purchase Cost',
    'landed_cost'               => 'Actual Landed Cost (Post-expenses)',
    'allocated_share'           => 'Allocated Share of Expenses',

    // Cost Centers
    'cost_center'               => 'Cost Center',
    'cc_rent'                   => 'Office & Store Rent',
    'cc_utilities'              => 'Utilities (Electricity, Water, Gas)',
    'cc_salaries'               => 'Salaries & Staff Wages',
    'cc_vehicles'               => 'Fuel, Oil & Van Maintenance',
    'cc_maintenance'            => 'Equipment & Store Maintenance',
    'cc_packaging'              => 'Printing, Bags & Cartons',
    'cc_hospitality'            => 'Hospitality & Cleaning Supplies',
    'cc_marketing'              => 'Marketing & Advertising',
    'cc_shipping'               => 'Freight & External Shipping',
    'cc_operational'            => 'General Operating Expenses',
    'total_month'               => 'Total Expenses This Month',
    'total_cash'                => 'Cash Drawn from Drawer',
    'total_filtered'            => 'Total Filtered Expenses',
    'new_expense'               => 'Record New Operating Expense 💸',
    'edit_expense'              => 'Edit Expense Details',
    'delete_confirm'            => 'Are you sure you want to delete expense (:title)?',
    'no_expenses'               => 'No recorded expenses matching search',
    'quick_category'            => 'Quick Category',
    'all_cost_centers'          => 'All Cost Centers',
    'all_payment_methods'       => 'All Payment Methods',
    'search_placeholder'        => '... Search by expense number, title, or category',
    'category'                  => 'Category',
    'recorded_success'          => 'Expense recorded in accounts successfully',
    'updated_success'           => 'Expense details updated successfully',
    'deleted_success'           => 'Expense moved to trash successfully',
];
