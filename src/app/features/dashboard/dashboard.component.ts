import {
  AfterViewInit,
  Component,
  ElementRef,
  ViewChild,
  computed,
  effect,
  signal,
  inject,
  OnInit
} from '@angular/core';
import { DatePipe, CommonModule } from '@angular/common';
import { Chart, ChartConfiguration, registerables } from 'chart.js';
import { AuthService } from '../../core/services/auth.service';
import { User, UserStatistics } from '../../core/models/user.interface';
import { FormControl, FormGroup, ReactiveFormsModule, Validators } from '@angular/forms';

Chart.register(...registerables);

interface DashboardUser {
  id: number;
  name: string;
  email: string;
  role: 'Admin' | 'User';
  status: 'active' | 'inactive';
  dateCreated: Date;
  totalNotes?: number;
  NotesFinished?: number;
}

@Component({
  selector: 'app-dashboard',
  imports: [CommonModule, ReactiveFormsModule, DatePipe],
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css',
  providers: [DatePipe],
})
export class DashboardComponent implements OnInit, AfterViewInit {
  @ViewChild('myChart') myChart!: ElementRef<HTMLCanvasElement>;
  @ViewChild('myChart2') myChart2!: ElementRef<HTMLCanvasElement>;

  // Inject services
  private datePipe = inject(DatePipe);
  private authService = inject(AuthService);

  // --------------------------------------------------------
  // USERS SIGNAL
  // --------------------------------------------------------
  users = signal<DashboardUser[]>([]);
  statistics = signal<any>(null);
  activityLogs = signal<any[]>([]);
  systemOverview = signal<any>(null);
  
  // Modal states
  showAddAdminModal = signal<boolean>(false);
  showStatisticsModal = signal<boolean>(false);
  showActivityLogsModal = signal<boolean>(false);
  showOverviewModal = signal<boolean>(false);
  
  // Add Admin Form
  addAdminForm = new FormGroup({
    full_name: new FormControl('', [Validators.required]),
    email: new FormControl('', [Validators.required, Validators.email]),
    password: new FormControl('', [Validators.required, Validators.minLength(6)])
  });


  // --------------------------------------------------------
  // COMPUTED SIGNALS
  // --------------------------------------------------------
  usersActiveCount = computed(() =>
    this.users().filter((u) => u.status === 'active').length
  );

  usersInactiveCount = computed(() =>
    this.users().filter((u) => u.status === 'inactive').length
  );

  // Accounts created in the last week
  usersLastWeek = computed(() => {
    const now = new Date();
    const oneWeekAgo = new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000);
    return this.users().filter((u) => {
      const createdDate = new Date(u.dateCreated);
      return createdDate >= oneWeekAgo && createdDate <= now;
    });
  });

  usersLastWeekCount = computed(() => this.usersLastWeek().length);

  // Daily breakdown for last week
  usersPerDayLastWeek = computed(() => {
    const now = new Date();
    const counts = new Map<string, number>();
    
    // Initialize all 7 days with 0
    for (let i = 6; i >= 0; i--) {
      const date = new Date(now);
      date.setDate(date.getDate() - i);
      date.setHours(0, 0, 0, 0);
      const key = date.toISOString().split('T')[0];
      counts.set(key, 0);
    }

    // Count users per day
    for (const user of this.usersLastWeek()) {
      const createdDate = new Date(user.dateCreated);
      createdDate.setHours(0, 0, 0, 0);
      const key = createdDate.toISOString().split('T')[0];
      counts.set(key, (counts.get(key) ?? 0) + 1);
    }

    const sortedKeys = Array.from(counts.keys()).sort();
    const labels = sortedKeys.map((key) => {
      const date = new Date(key);
      return this.datePipe.transform(date, 'EEE, MMM d')!;
    });
    const data = sortedKeys.map((key) => counts.get(key)!);
    const maxValue = data.length > 0 ? Math.max(...data) : 1;

    return { labels, data, maxValue };
  });

  // LAST 6 MONTHS ONLY
  usersPerMonth = computed(() => {
    const counts = new Map<string, number>();

    for (const user of this.users()) {
      const d = user.dateCreated;
      const key = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
      counts.set(key, (counts.get(key) ?? 0) + 1);
    }

    const sortedKeys = Array.from(counts.keys()).sort();
    const monthsToShow = 6;
    const recentKeys = sortedKeys.slice(-monthsToShow);

    const labels = recentKeys.map((key) => {
      const [year, month] = key.split('-').map(Number);
      return this.datePipe.transform(new Date(year, month - 1), 'MMM yy')!;
    });

    const data = recentKeys.map((key) => counts.get(key)!);

    return { labels, data };
  });

  private chart?: Chart;
  private chart2?: Chart;

  // --------------------------------------------------------
  // FETCH USERS FROM API
  // --------------------------------------------------------
  ngOnInit(): void {
    this.loadUsers();
    this.loadStatistics();
    this.loadActivityLogs();
    this.loadSystemOverview();
  }

  loadUsers() {
    this.authService.getUsers().subscribe({
      next: (response: any) => {
        // Transform API response to DashboardUser format
        const usersData = response.data || response || [];
        const transformedUsers: DashboardUser[] = usersData.map((user: any) => ({
          id: user.id,
          name: user.name || user.full_name || '',
          email: user.email,
          role: user.role === 'admin' || user.role === 'Admin' ? 'Admin' : 'User',
          status: user.status || (user.isActive ? 'active' : 'inactive'),
          dateCreated: user.created_at ? new Date(user.created_at) : new Date(),
          totalNotes: user.totalNotes || user.totalTasks || 0,
          NotesFinished: user.NotesFinished || user.completedTasks || 0
        }));
        this.users.set(transformedUsers);
      },
      error: (error) => {
        console.error('Error fetching users:', error);
      }
    });
  }

  loadStatistics() {
    this.authService.getUserStatistics().subscribe({
      next: (response: any) => {
        this.statistics.set(response.data || response);
      },
      error: (error) => {
        console.error('Error fetching statistics:', error);
      }
    });
  }

  loadActivityLogs() {
    this.authService.getActivityLogs().subscribe({
      next: (response: any) => {
        this.activityLogs.set(response.data || response || []);
      },
      error: (error) => {
        console.error('Error fetching activity logs:', error);
      }
    });
  }

  loadSystemOverview() {
    this.authService.getSystemOverview().subscribe({
      next: (response: any) => {
        this.systemOverview.set(response.data || response);
      },
      error: (error) => {
        console.error('Error fetching system overview:', error);
      }
    });
  }

  // --------------------------------------------------------
  // EFFECT: AUTO-UPDATE CHARTS WHEN SIGNALS CHANGE
  // --------------------------------------------------------
  private updateEffect = effect(() => {
    const active = this.usersActiveCount();
    const inactive = this.usersInactiveCount();
    const monthly = this.usersPerMonth();

    if (this.chart) {
      this.chart.data.datasets[0].data = [active, inactive];
      this.chart.update();
    }

    if (this.chart2) {
      this.chart2.data.labels = monthly.labels;
      this.chart2.data.datasets[0].data = monthly.data;
      this.chart2.update();
    }
  });

  ngAfterViewInit(): void {
    setTimeout(() => this.initCharts(), 0);
  }

  // --------------------------------------------------------
  // DELETE USER
  // --------------------------------------------------------
  deleteUser(id: number) {
    if (confirm('Are you sure you want to delete this user?')) {
      this.authService.deleteUser(id).subscribe({
        next: (response) => {
          console.log('User deleted successfully:', response);
          // Remove from local state after successful deletion
          this.users.update((users) => users.filter((u) => u.id !== id));
          this.loadStatistics();
          this.loadActivityLogs();
        },
        error: (error) => {
          console.error('Error deleting user:', error);
          alert('Failed to delete user');
        }
      });
    }
  }

  // --------------------------------------------------------
  // UPDATE USER ROLE
  // --------------------------------------------------------
  updateUserRole(userId: number, newRole: string) {
    this.authService.updateUserRole(userId, newRole).subscribe({
      next: (response) => {
        console.log('User role updated successfully:', response);
        // Update local state
        this.users.update((users) => 
          users.map((u) => 
            u.id === userId ? { ...u, role: newRole === 'admin' ? 'Admin' : 'User' } : u
          )
        );
        this.loadStatistics();
        this.loadActivityLogs();
      },
      error: (error) => {
        console.error('Error updating user role:', error);
        alert('Failed to update user role');
      }
    });
  }

  // --------------------------------------------------------
  // ADD NEW ADMIN
  // --------------------------------------------------------
  openAddAdminModal() {
    this.addAdminForm.reset();
    this.showAddAdminModal.set(true);
  }

  closeAddAdminModal() {
    this.showAddAdminModal.set(false);
    this.addAdminForm.reset();
  }

  addNewAdmin() {
    if (this.addAdminForm.valid) {
      const formData = this.addAdminForm.value;
      this.authService.addNewAdmin({
        full_name: formData.full_name,
        email: formData.email,
        password: formData.password
      }).subscribe({
        next: (response) => {
          console.log('Admin added successfully:', response);
          this.closeAddAdminModal();
          this.loadUsers();
          this.loadStatistics();
          this.loadActivityLogs();
          alert('Admin added successfully!');
        },
        error: (error) => {
          console.error('Error adding admin:', error);
          alert(error.error?.message || 'Failed to add admin');
        }
      });
    }
  }

  // --------------------------------------------------------
  // CHART INITIALIZATION
  // --------------------------------------------------------
  private initCharts(): void {
    const barConfig: ChartConfiguration<'bar'> = {
      type: 'bar',
      data: {
        labels: ['Active Users', 'Inactive Users'],
        datasets: [
          {
            label: 'Users',
            data: [this.usersActiveCount(), this.usersInactiveCount()],
            backgroundColor: ['#5B21B6', '#A78BFA'],
            borderColor: ['#4C1D95', '#8B5CF6'],
            borderWidth: 2,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false,
          },
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1,
            },
          },
        },
      },
    };

    const monthly = this.usersPerMonth();

    const lineConfig: ChartConfiguration<'line'> = {
      type: 'line',
      data: {
        labels: monthly.labels,
        datasets: [
          {
            label: 'Accounts Created (Last 6 Months)',
            data: monthly.data,
            backgroundColor: '#A78BFA',
            borderColor: '#5B21B6',
            fill: true,
            tension: 0.4,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
      },
    };

    this.chart = new Chart(this.myChart.nativeElement, barConfig);
    this.chart2 = new Chart(this.myChart2.nativeElement, lineConfig);
  }

  // Helper methods for template
  getObjectKeys(obj: any): string[] {
    return obj ? Object.keys(obj) : [];
  }

  formatKey(key: string): string {
    return key.replace(/_/g, ' ').toUpperCase();
  }

  stringifyValue(value: any): string {
    if (typeof value === 'object' && value !== null) {
      return JSON.stringify(value);
    }
    return String(value);
  }
}
