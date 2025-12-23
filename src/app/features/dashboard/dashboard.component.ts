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
import { DatePipe } from '@angular/common';
import { Chart, ChartConfiguration, registerables } from 'chart.js';
import { AuthService } from '../../core/services/auth.service';
import { User, UserStatistics } from '../../core/models/user.interface';

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


  // --------------------------------------------------------
  // COMPUTED SIGNALS
  // --------------------------------------------------------
  usersActiveCount = computed(() =>
    this.users().filter((u) => u.status === 'active').length
  );

  usersInactiveCount = computed(() =>
    this.users().filter((u) => u.status === 'inactive').length
  );

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
    this.users.update((users) => users.filter((u) => u.id !== id));
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
}
