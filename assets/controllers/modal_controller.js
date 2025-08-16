import { Controller } from "@hotwired/stimulus"
import { Modal } from "bootstrap"

export default class extends Controller {
    connect() {
        this.createBudgetModalInstance = null
        this.createTransactionModalInstance = null
    }

    openCreateBudget() {
        const modalElement = document.getElementById('createBudgetModal')
        if (!modalElement) return

        // Clear form
        const form = modalElement.querySelector('#createBudgetForm')
        if (form) {
            form.reset()
        }

        if (!this.createBudgetModalInstance) {
            this.createBudgetModalInstance = new Modal(modalElement)
        }
        
        this.createBudgetModalInstance.show()
    }

    openCreateTransaction(event) {
        const modalElement = document.getElementById('createTransactionModal')
        if (!modalElement) return

        // Get budget data from button attributes
        const button = event.currentTarget
        const budgetId = button.getAttribute('data-modal-budget-id-param')
        const budgetName = button.getAttribute('data-modal-budget-name-param')
        const sourceLocation = button.getAttribute('data-modal-source-param') || 'dashboard'

        // Set budget info in modal
        const budgetNameInput = modalElement.querySelector('#transaction_budget_name')
        const budgetIdInput = modalElement.querySelector('#transaction_budget_id')
        const sourceInput = modalElement.querySelector('#transaction_source')
        const modalTitle = modalElement.querySelector('#createTransactionModalLabel')
        
        if (budgetNameInput) budgetNameInput.value = budgetName
        if (budgetIdInput) budgetIdInput.value = budgetId
        if (sourceInput) sourceInput.value = sourceLocation
        if (modalTitle) modalTitle.innerHTML = `<i class="fas fa-plus me-2"></i>Nowa transakcja dla budżetu: ${budgetName}`

        // Set form action
        const form = modalElement.querySelector('#createTransactionForm')
        if (form) {
            form.setAttribute('action', `/budgets/${budgetId}/transactions/new`)
            // Clear other form fields
            const amountField = form.querySelector('#transaction_amount')
            const typeField = form.querySelector('#transaction_type')
            const commentField = form.querySelector('#transaction_comment')
            
            if (amountField) {
                amountField.value = ''
                // Add input restriction for amount field
                this.setupAmountField(amountField)
            }
            if (typeField) typeField.value = ''
            if (commentField) commentField.value = ''
        }

        if (!this.createTransactionModalInstance) {
            this.createTransactionModalInstance = new Modal(modalElement)
        }
        
        this.createTransactionModalInstance.show()
    }

    setupAmountField(field) {
        // Remove existing listeners to avoid duplicates
        field.removeEventListener('input', this.handleAmountInput)
        field.removeEventListener('keypress', this.handleAmountKeypress)
        
        // Add new listeners
        field.addEventListener('input', this.handleAmountInput.bind(this))
        field.addEventListener('keypress', this.handleAmountKeypress.bind(this))
    }

    handleAmountKeypress(event) {
        const char = String.fromCharCode(event.which)
        const value = event.target.value
        
        // Allow: backspace, delete, tab, escape, enter
        if ([8, 9, 27, 13, 46].indexOf(event.keyCode) !== -1 ||
            // Allow: Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
            (event.keyCode === 65 && event.ctrlKey === true) ||
            (event.keyCode === 67 && event.ctrlKey === true) ||
            (event.keyCode === 86 && event.ctrlKey === true) ||
            (event.keyCode === 88 && event.ctrlKey === true)) {
            return
        }
        
        // Allow only digits, comma, and dot
        if (!/[\d,.]/.test(char)) {
            event.preventDefault()
            return
        }
        
        // Allow only one decimal separator
        if ((char === ',' || char === '.') && (value.includes(',') || value.includes('.'))) {
            event.preventDefault()
        }
    }

    handleAmountInput(event) {
        let value = event.target.value
        
        // Replace commas with dots for normalization
        value = value.replace(/,/g, '.')
        
        // Remove multiple dots, keep only the first one
        const parts = value.split('.')
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('')
        }
        
        // Limit to 2 decimal places
        if (parts.length === 2 && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2)
        }
        
        // Update the field value
        event.target.value = value
    }
}