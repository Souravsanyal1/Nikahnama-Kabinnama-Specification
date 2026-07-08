// assets/js/main.js

document.addEventListener('DOMContentLoaded', function () {
    // 1. Multi-step Form Navigation (for create.php and edit.php)
    const nextBtns = document.querySelectorAll('.btn-next-tab');
    const prevBtns = document.querySelectorAll('.btn-prev-tab');
    
    if (nextBtns.length > 0) {
        nextBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const targetTabId = this.getAttribute('data-next');
                const nextTabLink = document.querySelector(`#formTabs button[data-bs-target="${targetTabId}"]`);
                if (nextTabLink) {
                    // Quick check of current section validation before proceeding
                    const currentPane = this.closest('.tab-pane');
                    const inputs = currentPane.querySelectorAll('input[required], select[required], textarea[required]');
                    let valid = true;
                    
                    inputs.forEach(input => {
                        if (!input.value.trim()) {
                            input.classList.add('is-invalid');
                            valid = false;
                        } else {
                            input.classList.remove('is-invalid');
                        }
                    });

                    if (valid) {
                        const tab = new bootstrap.Tab(nextTabLink);
                        tab.show();
                        window.scrollTo(0, 0);
                    } else {
                        // Display temporary alert
                        showToast('Please fill out all required fields in this section.', 'danger');
                    }
                }
            });
        });
    }

    if (prevBtns.length > 0) {
        prevBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                const targetTabId = this.getAttribute('data-prev');
                const prevTabLink = document.querySelector(`#formTabs button[data-bs-target="${targetTabId}"]`);
                if (prevTabLink) {
                    const tab = new bootstrap.Tab(prevTabLink);
                    tab.show();
                    window.scrollTo(0, 0);
                }
            });
        });
    }

    // 2. Real-time Search Handler on Dashboard
    const searchInput = document.getElementById('dashboardSearch');
    const searchResults = document.getElementById('searchResultsTable');

    if (searchInput && searchResults) {
        let timeout = null;
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            const query = this.value.trim();

            timeout = setTimeout(function () {
                fetch('dashboard.php?action=search&q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(data => {
                        let html = '';
                        if (data.length === 0) {
                            html = `<tr><td colspan="7" class="text-center py-4 text-muted">আপনার অনুসন্ধানের সাথে মেলে এমন কোনো রেকর্ড পাওয়া যায়নি।</td></tr>`;
                        } else {
                            data.forEach(item => {
                                let badgeClass = 'badge-paid';
                                let statusText = 'পরিশোধিত';
                                if (item.mahr_status === 'due') {
                                    badgeClass = 'badge-due';
                                    statusText = 'বকেয়া';
                                } else if (item.mahr_status === 'partially_paid') {
                                    badgeClass = 'badge-partial';
                                    statusText = 'আংশিক';
                                }

                                html += `
                                <tr>
                                    <td><strong class="text-primary">${item.certificate_no}</strong></td>
                                    <td>${item.groom_name}</td>
                                    <td>${item.bride_name}</td>
                                    <td>${item.marriage_date}</td>
                                    <td>${item.mahr_amount} ${item.currency}</td>
                                    <td><span class="badge ${badgeClass}">${statusText}</span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="view.php?id=${item.id}" class="btn btn-sm btn-outline-secondary">দেখুন</a>
                                            <a href="edit.php?id=${item.id}" class="btn btn-sm btn-outline-warning">সম্পাদনা</a>
                                            <a href="print.php?id=${item.id}" target="_blank" class="btn btn-sm btn-outline-primary">প্রিন্ট</a>
                                        </div>
                                    </td>
                                </tr>`;
                            });
                        }
                        searchResults.innerHTML = html;
                    })
                    .catch(err => {
                        console.error('Error fetching search results:', err);
                    });
            }, 300);
        });
    }

    // 3. Simple Dynamic Alert Toast System
    function showToast(message, type = 'info') {
        let container = document.getElementById('toastContainer');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toastContainer';
            container.style.position = 'fixed';
            container.style.top = '20px';
            container.style.right = '20px';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }

        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show shadow-lg`;
        toast.style.minWidth = '300px';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        container.appendChild(toast);

        // Auto remove after 4 seconds
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(toast);
            bsAlert.close();
        }, 4000);
    }
});
