const form = document.getElementById('transaction-form');
const tableBody = document.querySelector('#transaction-table tbody');
const totalIncomeEl = document.getElementById('total-income');
const totalExpenseEl = document.getElementById('total-expense');
const balanceEl = document.getElementById('balance');
const submitBtn = document.getElementById('submit-btn');
const cancelBtn = document.getElementById('cancel-btn');
const idField = document.getElementById('transaction-id');

let transactions = [];

function loadTransactions() {
    const data = localStorage.getItem('transactions');
    transactions = data ? JSON.parse(data) : [];
}

function saveTransactions() {
    localStorage.setItem('transactions', JSON.stringify(transactions));
}

function clearForm() {
    form.reset();
    idField.value = '';
    submitBtn.textContent = '新增';
    cancelBtn.classList.add('hidden');
}

function renderTransactions() {
    tableBody.innerHTML = '';
    transactions.forEach((t) => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${t.date}</td>
            <td>${t.description}</td>
            <td>${parseFloat(t.amount).toFixed(2)}</td>
            <td>${t.type === 'income' ? '收入' : '支出'}</td>
            <td>
                <button onclick="editTransaction('${t.id}')">编辑</button>
                <button onclick="deleteTransaction('${t.id}')">删除</button>
            </td>
        `;
        tableBody.appendChild(tr);
    });
    updateTotals();
}

function updateTotals() {
    let totalIncome = 0;
    let totalExpense = 0;
    transactions.forEach((t) => {
        if (t.type === 'income') {
            totalIncome += parseFloat(t.amount);
        } else {
            totalExpense += parseFloat(t.amount);
        }
    });
    totalIncomeEl.textContent = totalIncome.toFixed(2);
    totalExpenseEl.textContent = totalExpense.toFixed(2);
    balanceEl.textContent = (totalIncome - totalExpense).toFixed(2);
}

function addTransaction(data) {
    transactions.push(data);
    saveTransactions();
    renderTransactions();
}

function updateTransaction(data) {
    const index = transactions.findIndex((t) => t.id === data.id);
    if (index !== -1) {
        transactions[index] = data;
        saveTransactions();
        renderTransactions();
    }
}

function deleteTransaction(id) {
    transactions = transactions.filter((t) => t.id !== id);
    saveTransactions();
    renderTransactions();
}

function editTransaction(id) {
    const t = transactions.find((tr) => tr.id === id);
    if (!t) return;
    idField.value = t.id;
    document.getElementById('date').value = t.date;
    document.getElementById('description').value = t.description;
    document.getElementById('amount').value = t.amount;
    document.getElementById('type').value = t.type;
    submitBtn.textContent = '更新';
    cancelBtn.classList.remove('hidden');
}

form.addEventListener('submit', (e) => {
    e.preventDefault();
    const data = {
        id: idField.value || Date.now().toString(),
        date: document.getElementById('date').value,
        description: document.getElementById('description').value,
        amount: document.getElementById('amount').value,
        type: document.getElementById('type').value
    };
    if (idField.value) {
        updateTransaction(data);
    } else {
        addTransaction(data);
    }
    clearForm();
});

cancelBtn.addEventListener('click', (e) => {
    clearForm();
});

loadTransactions();
renderTransactions();
