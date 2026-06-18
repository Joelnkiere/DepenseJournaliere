            </div>
        </div>
    </main>

    <footer class="app-footer">
        <div class="float-end d-none d-sm-inline">BudgetPro</div>
        <strong>Suivi financier personnel</strong>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@4/dist/js/adminlte.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="../assets/js/app.js"></script>
<script>
function setTheme(theme) {
    document.documentElement.setAttribute('data-bs-theme', theme);
    localStorage.setItem('bm_theme', theme);

    const lightBtn = document.getElementById('themeLightBtn');
    const darkBtn  = document.getElementById('themeDarkBtn');
    if (lightBtn && darkBtn) {
        if (theme === 'light') {
            lightBtn.classList.add('btn-primary');
            lightBtn.classList.remove('btn-outline-secondary');
            darkBtn.classList.add('btn-outline-secondary');
            darkBtn.classList.remove('btn-primary');
        } else {
            darkBtn.classList.add('btn-primary');
            darkBtn.classList.remove('btn-outline-secondary');
            lightBtn.classList.add('btn-outline-secondary');
            lightBtn.classList.remove('btn-primary');
        }
    }

    const sun  = document.getElementById('themeIconSun');
    const moon = document.getElementById('themeIconMoon');
    if (sun)  sun.style.display  = theme === 'light' ? '' : 'none';
    if (moon) moon.style.display = theme === 'dark'  ? '' : 'none';

    if (typeof API_BASE !== 'undefined') {
        const fd = new FormData();
        fd.append('theme', theme);
        fetch(API_BASE + 'api/user.php?action=update_theme', { method:'POST', body:fd }).catch(()=>{});
    }
}

const headerThemeToggle = document.getElementById('headerThemeToggle');
if (headerThemeToggle) {
    headerThemeToggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-bs-theme') || 'dark';
        setTheme(current === 'dark' ? 'light' : 'dark');
    });
}

(function() {
    const saved = localStorage.getItem('bm_theme');
    if (saved && saved !== document.documentElement.getAttribute('data-bs-theme')) {
        setTheme(saved);
    }
})();
</script>
</body>
</html>
