<button id="theme-toggle" class="theme-btn">
    🌙 Dark Mode
</button>

<style>
    .theme-btn {
        padding: 12px 20px;
        border: none;
        border-radius: 50px;
        background: #6366f1;
        color: white;
        cursor: pointer;
        font-weight: 600;
        box-shadow: 0 8px 20px rgba(99, 102, 241, .25);
        transition: .3s;
    }

    .theme-btn:hover {
        transform: translateY(-3px);
    }

    :root {
        --bg: #f5f7fb;
        --text: #1e293b;
        --card: #ffffff;
        --border: #edf2f7;
        --secondary: #64748b;
    }

    body.dark {
        --bg: #0f172a;
        --text: #f8fafc;
        --card: #1e293b;
        --border: #334155;
        --secondary: #94a3b8;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {

        const btn = document.getElementById('theme-toggle');

        if (localStorage.getItem('theme') === 'dark') {
            document.body.classList.add('dark');
            btn.innerHTML = '☀️ Light Mode';
        }

        btn.addEventListener('click', () => {

            document.body.classList.toggle('dark');

            if (document.body.classList.contains('dark')) {
                localStorage.setItem('theme', 'dark');
                btn.innerHTML = '☀️ Light Mode';
            } else {
                localStorage.setItem('theme', 'light');
                btn.innerHTML = '🌙 Dark Mode';
            }

        });
    });
</script>