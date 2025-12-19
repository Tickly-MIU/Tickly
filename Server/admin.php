<!doctype html>
<html lang="en" class="dark">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>ToDo Admin — Dashboard</title>
  <meta name="description" content="Tailwind admin dashboard — front-end only. Dark mode default. Two visual styles: Professional & Colorful." />

  <!-- Tailwind Play CDN (no build step) -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Tailwind config: enable class-based dark mode
    tailwind.config = {
      darkMode: 'class',
      theme: { extend: { } }
    }
  </script>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    /* Small helpers for the two style variants. We'll toggle the .variant-colorful class on body. */
    :root {
      --accent-1: #60a5fa; /* blue */
      --accent-2: #f97316; /* orange */
      --bg-panel: rgba(255,255,255,0.04);
      --card-shadow: 0 6px 18px rgba(15,23,42,0.35);
    }
    .variant-colorful {
      --accent-1: linear-gradient(135deg,#7c3aed 0%,#06b6d4 100%);
      --accent-2: linear-gradient(135deg,#f97316 0%,#f43f5e 100%);
    }
    /* For colorful variant we add a subtle gradient backdrop */
    .variant-colorful .hero-gradient {
      background: linear-gradient(90deg, rgba(124,58,237,0.14), rgba(6,182,212,0.08));
      backdrop-filter: blur(6px);
    }
    /* Professional: flat clean look */
    .variant-professional .accent { background: rgba(96,165,250,0.12); color: #93c5fd }

    /* Ensure charts blend in dark mode */
    .chart-legend { color: rgba(203,213,225,0.9); }

    /* Mobile sidebar overlay */
    #sidebar-overlay {
      transition: opacity 0.3s ease;
    }
    
    /* Sidebar animation */
    #sidebar {
      transition: transform 0.3s ease;
    }
    
    /* Hide sidebar by default on mobile */
    @media (max-width: 768px) {
      #sidebar {
        transform: translateX(-100%);
        background-color: #1f2937;
      }
      #sidebar.mobile-open {
        transform: translateX(0);
      }
      
      /* Mobile-specific navigation styling */
      #sidebar nav {
        @apply space-y-2;
      }
      
      #sidebar nav a {
        @apply flex items-center gap-3 px-4 py-3 rounded-lg text-base;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.2s ease;
      }
      
      #sidebar nav a:hover {
        background: rgba(96, 165, 250, 0.15);
        border-color: rgba(96, 165, 250, 0.3);
        transform: translateX(5px);
      }
      
      #sidebar nav a span {
        @apply font-medium;
      }
    }
    
    /* Fix for pie chart container */
    .pie-chart-container {
      height: 240px;
      position: relative;
    }
  </style>
</head>
<body class="min-h-screen bg-slate-900 text-slate-200 variant-professional">

  <div id="app" class="flex flex-col md:flex-row">
    <!-- Mobile menu button -->
    <div class="md:hidden flex items-center justify-between p-4 bg-slate-800/60 border-b border-slate-700">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-600 to-sky-400 flex items-center justify-center font-bold">TD</div>
        <div>
          <div class="font-semibold text-lg">ToDo Admin</div>
        </div>
      </div>
      <button id="mobile-menu-toggle" class="p-2 rounded-md bg-slate-700 hover:bg-slate-600">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
    </div>

    <!-- Sidebar Overlay (for mobile) -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-20 hidden md:hidden"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="w-72 bg-slate-850/95 border-r border-slate-800 p-4 space-y-6 min-h-screen fixed md:static left-0 top-0 bottom-0 z-30 md:z-auto overflow-y-auto">
      <div class="flex items-center justify-between md:block">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-indigo-600 to-sky-400 flex items-center justify-center font-bold">TD</div>
          <div>
            <div class="font-semibold text-lg">ToDo Admin</div>
            <div class="text-xs text-slate-400">Management panel</div>
          </div>
        </div>
        <button id="close-sidebar" class="md:hidden p-2 rounded-md bg-slate-700 hover:bg-slate-600">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>

      <!-- <nav class="space-y-1">
        <a class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-slate-800/50 transition-colors" href="#">
          <span class="text-lg">🏠</span>
          <span class="ml-1">Overview</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-slate-800/50 transition-colors" href="#">
          <span class="text-lg">👥</span>
          <span class="ml-1">Users</span>
        </a>
         <a class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-slate-800/50 transition-colors" href="#">
          <span class="text-lg">✅</span>
          <span class="ml-1">Tasks</span>
        </a> 
        <a class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-slate-800/50 transition-colors" href="#">
          <span class="text-lg">📊</span>
          <span class="ml-1">Reports</span>
        </a>
        <a class="flex items-center gap-3 px-3 py-2 rounded-md hover:bg-slate-800/50 transition-colors" href="#">
          <span class="text-lg">⚙️</span>
          <span class="ml-1">Settings</span>
        </a>
      </nav> -->

    </aside>

    <!-- Main content -->
    <main class="flex-1 md:ml-0 p-4 md:p-6 w-full">
      <!-- Topbar -->
      <header class="flex flex-col md:flex-row md:items-center justify-between mb-6 gap-4">
        <div>
          <h1 class="text-2xl font-semibold">Dashboard</h1>
          <p class="text-sm text-slate-400">Overview of system activity</p>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
          <div class="relative flex-1">
            <input id="search" class="w-full py-2 px-3 rounded-md bg-slate-800/40 placeholder-slate-400 focus:outline-none" placeholder="Search tasks, users..." />
            <button id="clear-search" class="absolute right-1 top-1.5 text-slate-400 text-sm">Clear</button>
          </div>
          <button id="create" class="px-4 py-2 rounded-md bg-indigo-600 hover:bg-indigo-500 whitespace-nowrap">Create Task</button>
          <div class="w-10 h-10 rounded-full bg-indigo-700 flex items-center justify-center">AZ</div>
        </div>
      </header>

      <!-- Stats -->
      <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-slate-800/40 p-4 rounded-2xl border border-slate-700">
          <div class="text-xs text-slate-400">Total Users</div>
          <div class="text-2xl font-semibold">1,284</div>
          <div class="text-sm text-slate-400">Active accounts</div>
        </div>
        <div class="bg-slate-800/40 p-4 rounded-2xl border border-slate-700">
          <div class="text-xs text-slate-400">Total Tasks</div>
          <div class="text-2xl font-semibold">7,450</div>
          <div class="text-sm text-slate-400">All tasks</div>
        </div>
        <div class="bg-slate-800/40 p-4 rounded-2xl border border-slate-700">
          <div class="text-xs text-slate-400">Completed</div>
          <div class="text-2xl font-semibold">5,320</div>
          <div class="text-sm text-slate-400">Finished tasks</div>
        </div>
        <div class="bg-slate-800/40 p-4 rounded-2xl border border-slate-700">
          <div class="text-xs text-slate-400">Pending</div>
          <div class="text-2xl font-semibold">2,130</div>
          <div class="text-sm text-slate-400">Needs attention</div>
        </div>
      </section>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-slate-800/40 p-5 rounded-2xl border border-slate-700 hero-gradient">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-2">
            <h3 class="font-semibold">Activity (Last 30 days)</h3>
            <div class="text-sm text-slate-400">Updated just now</div>
          </div>
          <canvas id="areaChart" class="w-full h-56"></canvas>

        </div>

        <div class="bg-slate-800/40 p-5 rounded-2xl border border-slate-700">
          <h3 class="font-semibold mb-2">Tasks Breakdown</h3>
          <div class="pie-chart-container">
            <canvas id="pieChart"></canvas>
          </div>
        </div>
      </div>

      <!-- <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6"> -->
      <div class="grid grid-cols-1 lg:grid-cols-1 gap-6 mt-6">
        <div class="bg-slate-800/40 p-4 rounded-2xl border border-slate-700 overflow-auto">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-2">
            <h4 class="font-semibold">Users</h4>
            <div class="text-sm text-slate-400">Showing 8 users</div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full text-sm table-auto min-w-[500px]">
              <thead class="text-slate-400 text-xs">
                <tr>
                  <th class="text-left py-2">Name</th>
                  <th class="text-left py-2">Email</th>
                  <th class="text-left py-2">Role</th>
                  <th class="text-left py-2">Joined</th>
                </tr>
              </thead>
              <tbody id="usersTbody">
                <!-- Populated by JS -->
              </tbody>
            </table>
          </div>
        </div>

        <!-- <div class="bg-slate-800/40 p-4 rounded-2xl border border-slate-700">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-3 gap-2">
            <h4 class="font-semibold">Tasks</h4>
            <div class="flex items-center gap-3">
              <select id="statusFilter" class="bg-slate-800/30 rounded-md py-1 px-2 text-sm">
                <option value="all">All</option>
                <option value="pending">Pending</option>
                <option value="completed">Completed</option>
              </select>
              <button id="resetBtn" class="text-sm text-slate-400">Reset</button>
            </div>
          </div>

          <div id="tasksList" class="space-y-3"> -->
            <!-- Populated by JS -->
          </div>
        </div>
      </div>

      <footer class="mt-8 text-center text-sm text-slate-500">© <?php echo date('Y'); ?> ToDo Admin • Front-end Prototype</footer>
    </main>
  </div>

  <script>
    // Mobile sidebar functionality
    const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
    const closeSidebar = document.getElementById('close-sidebar');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    function openSidebar() {
      sidebar.classList.add('mobile-open');
      sidebarOverlay.classList.remove('hidden');
      document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }
    
    function closeMobileSidebar() {
      sidebar.classList.remove('mobile-open');
      sidebarOverlay.classList.add('hidden');
      document.body.style.overflow = ''; // Restore scrolling
    }
    
    mobileMenuToggle.addEventListener('click', openSidebar);
    closeSidebar.addEventListener('click', closeMobileSidebar);
    sidebarOverlay.addEventListener('click', closeMobileSidebar);
    
    // Close sidebar when clicking on a nav link (mobile)
    document.querySelectorAll('nav a').forEach(link => {
      link.addEventListener('click', closeMobileSidebar);
    });

    // Close sidebar when pressing Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') {
        closeMobileSidebar();
      }
    });

    // ---------- Mock data ----------
    const mockUsers = [
      { id: 1001, name: 'Aisha', email: 'aisha@mail.com', role: 'admin', created_at: '2025-01-03' },
      { id: 1002, name: 'Karim', email: 'karim@mail.com', role: 'user', created_at: '2025-02-12' },
      { id: 1003, name: 'Mona', email: 'mona@mail.com', role: 'user', created_at: '2025-03-05' },
      { id: 1004, name: 'Omar', email: 'omar@mail.com', role: 'user', created_at: '2025-04-20' },
      { id: 1005, name: 'Layla', email: 'layla@mail.com', role: 'user', created_at: '2025-05-09' },
      { id: 1006, name: 'Youssef', email: 'youssef@mail.com', role: 'user', created_at: '2025-06-11' },
      { id: 1007, name: 'Noura', email: 'noura@mail.com', role: 'user', created_at: '2025-07-21' },
      { id: 1008, name: 'Tamer', email: 'tamer@mail.com', role: 'user', created_at: '2025-08-30' },
    ];

    const mockTasks = [
      { id: 501, title: 'Fix bug #21', user: 'Aisha', priority: 'high', status: 'completed', due_date: '2025-10-05' },
      { id: 502, title: 'Write blog post', user: 'Karim', priority: 'medium', status: 'pending', due_date: '2025-10-12' },
      { id: 503, title: 'Design landing page', user: 'Mona', priority: 'high', status: 'pending', due_date: '2025-10-08' },
      { id: 504, title: 'Prepare presentation', user: 'Omar', priority: 'low', status: 'completed', due_date: '2025-10-02' },
      { id: 505, title: 'Database backup', user: 'Layla', priority: 'high', status: 'pending', due_date: '2025-10-20' },
      { id: 506, title: 'Update icons', user: 'Youssef', priority: 'low', status: 'completed', due_date: '2025-10-15' },
      { id: 507, title: 'Email campaign', user: 'Noura', priority: 'medium', status: 'pending', due_date: '2025-10-25' },
      { id: 508, title: 'Refactor auth', user: 'Tamer', priority: 'high', status: 'pending', due_date: '2025-10-18' },
      { id: 509, title: 'Test payment flow', user: 'Aisha', priority: 'medium', status: 'completed', due_date: '2025-10-10' },
      { id: 510, title: 'Plan sprint', user: 'Karim', priority: 'low', status: 'pending', due_date: '2025-10-22' },
    ];

    // ---------- Render users & tasks ----------
    const usersTbody = document.getElementById('usersTbody');
    // const tasksList = document.getElementById('tasksList');

    function renderUsers() {
      usersTbody.innerHTML = '';
      mockUsers.forEach(u => {
        const tr = document.createElement('tr');
        tr.className = 'border-t border-slate-700';
        tr.innerHTML = `
          <td class="py-3 flex items-center gap-3"> <div class="w-8 h-8 bg-indigo-700 rounded-full flex items-center justify-center">${u.name[0]}</div> <div>${u.name}</div> </td>
          <td class="py-3">${u.email}</td>
          <td class="py-3"><span class="px-2 py-1 rounded-full text-xs ${u.role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-700 text-slate-200'}">${u.role}</span></td>
          <td class="py-3">${u.created_at}</td>
        `;
        usersTbody.appendChild(tr);
      });
    }

    // initial render
    renderUsers();

    // ---------- Charts ----------
    const areaCtx = document.getElementById('areaChart').getContext('2d');
    const pieCtx = document.getElementById('pieChart').getContext('2d');

    const activityLabels = ['09-01','09-05','09-10','09-15','09-20','09-25','09-30'];
    const activityValues = [30,65,48,82,54,91,70];

    const areaChart = new Chart(areaCtx, {
      type: 'line',
      data: {
        labels: activityLabels,
        datasets: [{
          label: 'Tasks',
          data: activityValues,
          fill: true,
          tension: 0.4,
          backgroundColor: 'rgba(96,165,250,0.12)',
          borderColor: 'rgba(96,165,250,0.9)',
          pointBackgroundColor: 'rgba(96,165,250,0.9)'
        }]
      },
      options: {
        responsive:true,
        maintainAspectRatio: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { ticks: { color: 'rgba(203,213,225,0.8)' }, grid: { color: 'rgba(148,163,184,0.06)' } },
          y: { ticks: { color: 'rgba(203,213,225,0.8)' }, grid: { color: 'rgba(148,163,184,0.06)' } }
        }
      }
    });

    // Fixed pie chart with proper container constraints
    const pieChart = new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: ['Completed','Pending'],
        datasets: [{ 
          data: [5320,2130], 
          backgroundColor: ['rgba(96,165,250,0.9)','rgba(249,115,22,0.9)'],
          borderWidth: 0
        }]
      },
      options: { 
        responsive: true,
        maintainAspectRatio: true,
        layout: {
          padding: 10
        },
        plugins: { 
          legend: { 
            position: 'bottom', 
            labels: { 
              color: 'rgba(203,213,225,0.9)',
              padding: 15,
              usePointStyle: true,
              pointStyle: 'circle'
            } 
          } 
        } 
      }
    });

    // Force chart resize on window resize to prevent layout issues
    window.addEventListener('resize', function() {
      areaChart.resize();
      pieChart.resize();
    });

  </script>
</body>
</html>