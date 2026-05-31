// ============================================
// StayEase - Main JavaScript
// ============================================

// ---- AJAX: Toggle Interest (Heart Button) ----
function toggleInterest(btn, propertyId) {
    fetch('/student-accommodation/ajax/toggle_interest.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'property_id=' + propertyId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const icon = btn.querySelector('i');
            if (data.action === 'added') {
                btn.classList.add('interested');
                icon.classList.remove('bi-heart');
                icon.classList.add('bi-heart-fill');
                btn.title = 'Remove Interest';
                // If on detail page, update button text
                if (btn.id === 'interest-btn') {
                    btn.classList.remove('btn-outline-danger');
                    btn.classList.add('btn-danger');
                    btn.innerHTML = '<i class="bi bi-heart-fill me-2"></i>Remove Interest';
                    btn.onclick = () => toggleInterest(btn, propertyId);
                }
                showToast('Added to your interested list!', 'success');
            } else {
                btn.classList.remove('interested');
                icon.classList.remove('bi-heart-fill');
                icon.classList.add('bi-heart');
                btn.title = 'Mark as Interested';
                if (btn.id === 'interest-btn') {
                    btn.classList.remove('btn-danger');
                    btn.classList.add('btn-outline-danger');
                    btn.innerHTML = '<i class="bi bi-heart me-2"></i>Mark as Interested';
                    btn.onclick = () => toggleInterest(btn, propertyId);
                }
                showToast('Removed from your interested list.', 'info');
            }
        } else if (data.redirect) {
            window.location.href = data.redirect;
        } else {
            showToast('Something went wrong. Please try again.', 'danger');
        }
    })
    .catch(() => showToast('Network error. Please check your connection.', 'danger'));
}

// ---- AJAX: Apply Filters ----
function applyFilters() {
    const city   = document.getElementById('filter-city').value;
    const budget = document.getElementById('filter-budget').value;
    const gender = document.getElementById('filter-gender').value;

    const spinner = document.getElementById('loading-spinner');
    const grid    = document.getElementById('properties-grid');
    const noRes   = document.getElementById('no-results');
    const count   = document.getElementById('results-count');

    spinner.style.display = 'block';
    grid.style.opacity = '0.4';
    noRes.style.display = 'none';

    const params = new URLSearchParams({ city, budget, gender });

    fetch('/student-accommodation/ajax/filter_properties.php?' + params)
    .then(res => res.json())
    .then(data => {
        spinner.style.display = 'none';
        grid.style.opacity = '1';

        if (data.success) {
            grid.innerHTML = data.html;
            const num = data.count;
            count.innerHTML = `Showing <span class="text-primary">${num}</span> propert${num === 1 ? 'y' : 'ies'}`;
            if (num === 0) noRes.style.display = 'block';
        } else {
            showToast('Filter failed. Please try again.', 'danger');
        }
    })
    .catch(() => {
        spinner.style.display = 'none';
        grid.style.opacity = '1';
        showToast('Network error.', 'danger');
    });
}

// ---- Allow pressing Enter on filter inputs ----
document.addEventListener('DOMContentLoaded', () => {
    ['filter-city', 'filter-budget', 'filter-gender'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('keypress', e => { if (e.key === 'Enter') applyFilters(); });
    });
});

// ---- Toast Notification ----
function showToast(message, type = 'success') {
    const colors = {
        success: '#22c55e',
        info: '#3b82f6',
        danger: '#ef4444',
        warning: '#f59e0b'
    };

    const toast = document.createElement('div');
    toast.style.cssText = `
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        background: ${colors[type] || colors.info}; color: #fff;
        padding: 12px 20px; border-radius: 10px;
        font-size: 0.88rem; font-weight: 500;
        box-shadow: 0 4px 16px rgba(0,0,0,0.15);
        transform: translateY(20px); opacity: 0;
        transition: all 0.3s ease;
        max-width: 300px;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);

    setTimeout(() => { toast.style.transform = 'translateY(0)'; toast.style.opacity = '1'; }, 10);
    setTimeout(() => {
        toast.style.transform = 'translateY(20px)'; toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
