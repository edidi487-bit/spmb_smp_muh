// Main JavaScript logic for SPMB SMP Muhammadiyah 1 Pringsewu

document.addEventListener('DOMContentLoaded', function () {
    // 1. Loading Overlay Hide
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay) {
        window.addEventListener('load', function () {
            setTimeout(function() {
                loadingOverlay.style.opacity = 0;
                setTimeout(() => loadingOverlay.style.display = 'none', 300);
            }, 200);
        });
        // Safety timeout if load event doesn't trigger
        setTimeout(function() {
            loadingOverlay.style.opacity = 0;
            setTimeout(() => loadingOverlay.style.display = 'none', 300);
        }, 1500);
    }

    // 2. Dark Mode Toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    if (darkModeToggle) {
        // Check local storage for preference
        if (localStorage.getItem('dark-mode') === 'true') {
            document.body.classList.add('dark-mode');
            updateDarkModeIcon(true);
        } else {
            updateDarkModeIcon(false);
        }

        darkModeToggle.addEventListener('click', function () {
            const isDark = document.body.classList.toggle('dark-mode');
            localStorage.setItem('dark-mode', isDark);
            updateDarkModeIcon(isDark);
        });
    }

    function updateDarkModeIcon(isDark) {
        const icon = darkModeToggle ? darkModeToggle.querySelector('i') : null;
        if (icon) {
            if (isDark) {
                icon.className = 'bi bi-sun-fill';
                icon.style.color = '#f39c12';
            } else {
                icon.className = 'bi bi-moon-stars-fill';
                icon.style.color = '';
            }
        }
    }

    // 3. Navbar scroll effect for Landing Page
    const navbar = document.querySelector('.navbar-custom');
    if (navbar) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }

    // 4. Sidebar Collapse Toggle
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            sidebar.classList.toggle('collapsed');
        });
    }

    // 5. Bootstrap Form Validation
    const forms = document.querySelectorAll('.needs-validation');
    Array.prototype.slice.call(forms).forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });
});

// Toast notification helper
function showToast(icon, title) {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    Toast.fire({
        icon: icon,
        title: title
    });
}
