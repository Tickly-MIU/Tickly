import {
  AfterViewInit,
  Component,
  ElementRef,
  ViewChild,
  computed,
  effect,
  signal,
  inject
} from '@angular/core';
import { DatePipe } from '@angular/common';
import { Chart, ChartConfiguration, registerables } from 'chart.js';

Chart.register(...registerables);

interface User {
  id: number;
  name: string;
  email: string;
  role: 'Admin' | 'User';
  status: 'active' | 'inactive';
  dateCreated: Date;
  totalNotes: number;
  NotesFinished: number;
}

@Component({
  selector: 'app-dashboard',
  templateUrl: './dashboard.component.html',
  styleUrl: './dashboard.component.css',
  providers: [DatePipe],
})
export class DashboardComponent implements AfterViewInit {
  @ViewChild('myChart') myChart!: ElementRef<HTMLCanvasElement>;
  @ViewChild('myChart2') myChart2!: ElementRef<HTMLCanvasElement>;

  // Inject DatePipe (NO constructor needed)
  private datePipe = inject(DatePipe);

  // --------------------------------------------------------
  // USERS SIGNAL
  // --------------------------------------------------------
users = signal<User[]>([
  { id: 1,  name: "Liam Brown",        email: "liam.brown@example.com",        role: "User",  status: "active",   dateCreated: new Date("2023-10-02"), totalNotes: 12, NotesFinished: 8 },
  { id: 2,  name: "Emma Davis",        email: "emma.davis@example.com",        role: "Admin", status: "inactive", dateCreated: new Date("2023-10-05"), totalNotes: 20, NotesFinished: 15 },
  { id: 3,  name: "Noah Wilson",       email: "noah.wilson@example.com",       role: "User",  status: "active",   dateCreated: new Date("2023-10-11"), totalNotes: 9,  NotesFinished: 7 },
  { id: 4,  name: "Olivia Martin",     email: "olivia.martin@example.com",     role: "User",  status: "inactive", dateCreated: new Date("2023-10-19"), totalNotes: 5,  NotesFinished: 2 },
  { id: 5,  name: "Ava Thompson",      email: "ava.thompson@example.com",      role: "Admin", status: "active",   dateCreated: new Date("2023-10-27"), totalNotes: 17, NotesFinished: 12 },

  { id: 6,  name: "Ethan Garcia",      email: "ethan.garcia@example.com",      role: "User",  status: "active",   dateCreated: new Date("2023-11-01"), totalNotes: 14, NotesFinished: 10 },
  { id: 7,  name: "Mia Rodriguez",     email: "mia.rodriguez@example.com",     role: "User",  status: "inactive", dateCreated: new Date("2023-11-04"), totalNotes: 6,  NotesFinished: 3 },
  { id: 8,  name: "Lucas Hernandez",   email: "lucas.hernandez@example.com",   role: "Admin", status: "active",   dateCreated: new Date("2023-11-09"), totalNotes: 10, NotesFinished: 7 },
  { id: 9,  name: "Sophia Lopez",      email: "sophia.lopez@example.com",      role: "User",  status: "active",   dateCreated: new Date("2023-11-15"), totalNotes: 8,  NotesFinished: 5 },
  { id: 10, name: "James Gonzalez",    email: "james.gonzalez@example.com",    role: "User",  status: "inactive", dateCreated: new Date("2023-11-21"), totalNotes: 4,  NotesFinished: 1 },
  { id: 11, name: "Benjamin Perez",    email: "benjamin.perez@example.com",    role: "User",  status: "active",   dateCreated: new Date("2023-11-28"), totalNotes: 12, NotesFinished: 6 },

  { id: 12, name: "Charlotte Miller",  email: "charlotte.miller@example.com",  role: "Admin", status: "active",   dateCreated: new Date("2023-12-03"), totalNotes: 16, NotesFinished: 14 },
  { id: 13, name: "Henry Martinez",    email: "henry.martinez@example.com",    role: "User",  status: "inactive", dateCreated: new Date("2023-12-07"), totalNotes: 7,  NotesFinished: 3 },
  { id: 14, name: "Amelia Anderson",   email: "amelia.anderson@example.com",   role: "User",  status: "active",   dateCreated: new Date("2023-12-12"), totalNotes: 10, NotesFinished: 5 },
  { id: 15, name: "Alexander Taylor",  email: "alexander.taylor@example.com",  role: "Admin", status: "active",   dateCreated: new Date("2023-12-18"), totalNotes: 11, NotesFinished: 8 },
  { id: 16, name: "Harper Thomas",     email: "harper.thomas@example.com",     role: "User",  status: "inactive", dateCreated: new Date("2023-12-22"), totalNotes: 5,  NotesFinished: 2 },
  { id: 17, name: "William Jackson",   email: "william.jackson@example.com",   role: "User",  status: "active",   dateCreated: new Date("2023-12-26"), totalNotes: 18, NotesFinished: 12 },
  { id: 18, name: "Evelyn White",      email: "evelyn.white@example.com",      role: "Admin", status: "inactive", dateCreated: new Date("2023-12-30"), totalNotes: 14, NotesFinished: 10 },

  { id: 19, name: "Daniel Harris",     email: "daniel.harris@example.com",     role: "User",  status: "active",   dateCreated: new Date("2024-01-02"), totalNotes: 9,  NotesFinished: 4 },
  { id: 20, name: "Grace Clark",       email: "grace.clark@example.com",       role: "User",  status: "active",   dateCreated: new Date("2024-01-05"), totalNotes: 7,  NotesFinished: 3 },
  { id: 21, name: "Elijah Lewis",      email: "elijah.lewis@example.com",      role: "User",  status: "inactive", dateCreated: new Date("2024-01-09"), totalNotes: 3,  NotesFinished: 1 },
  { id: 22, name: "Chloe Lee",         email: "chloe.lee@example.com",         role: "Admin", status: "active",   dateCreated: new Date("2024-01-13"), totalNotes: 19, NotesFinished: 16 },
  { id: 23, name: "Logan Walker",      email: "logan.walker@example.com",      role: "User",  status: "active",   dateCreated: new Date("2024-01-17"), totalNotes: 12, NotesFinished: 9 },
  { id: 24, name: "Aria Hall",         email: "aria.hall@example.com",         role: "User",  status: "inactive", dateCreated: new Date("2024-01-21"), totalNotes: 8,  NotesFinished: 6 },
  { id: 25, name: "Jackson Young",     email: "jackson.young@example.com",     role: "User",  status: "active",   dateCreated: new Date("2024-01-24"), totalNotes: 10, NotesFinished: 8 },
  { id: 26, name: "Scarlett Allen",    email: "scarlett.allen@example.com",    role: "User",  status: "active",   dateCreated: new Date("2024-01-29"), totalNotes: 6,  NotesFinished: 4 },

  { id: 27, name: "Sebastian King",    email: "sebastian.king@example.com",    role: "Admin", status: "inactive", dateCreated: new Date("2024-02-01"), totalNotes: 13, NotesFinished: 9 },
  { id: 28, name: "Lily Wright",       email: "lily.wright@example.com",       role: "User",  status: "active",   dateCreated: new Date("2024-02-06"), totalNotes: 9,  NotesFinished: 5 },
  { id: 29, name: "Mateo Scott",       email: "mateo.scott@example.com",       role: "User",  status: "inactive", dateCreated: new Date("2024-02-11"), totalNotes: 8,  NotesFinished: 4 },
  { id: 30, name: "Hannah Green",      email: "hannah.green@example.com",      role: "User",  status: "active",   dateCreated: new Date("2024-02-16"), totalNotes: 15, NotesFinished: 10 },
  { id: 31, name: "Jacob Adams",       email: "jacob.adams@example.com",       role: "User",  status: "active",   dateCreated: new Date("2024-02-20"), totalNotes: 12, NotesFinished: 7 },
  { id: 32, name: "Zoey Baker",        email: "zoey.baker@example.com",        role: "Admin", status: "inactive", dateCreated: new Date("2024-02-24"), totalNotes: 14, NotesFinished: 9 },
  { id: 33, name: "Michael Nelson",    email: "michael.nelson@example.com",    role: "User",  status: "active",   dateCreated: new Date("2024-02-28"), totalNotes: 7,  NotesFinished: 5 },

  { id: 34, name: "Layla Carter",      email: "layla.carter@example.com",      role: "User",  status: "inactive", dateCreated: new Date("2024-03-02"), totalNotes: 4,  NotesFinished: 2 },
  { id: 35, name: "Samuel Mitchell",   email: "samuel.mitchell@example.com",   role: "User",  status: "active",   dateCreated: new Date("2024-03-06"), totalNotes: 11, NotesFinished: 7 },
  { id: 36, name: "Nora Perez",        email: "nora.perez@example.com",        role: "User",  status: "active",   dateCreated: new Date("2024-03-10"), totalNotes: 9,  NotesFinished: 6 },
  { id: 37, name: "David Roberts",     email: "david.roberts@example.com",     role: "Admin", status: "inactive", dateCreated: new Date("2024-03-14"), totalNotes: 18, NotesFinished: 12 },
  { id: 38, name: "Riley Turner",      email: "riley.turner@example.com",      role: "User",  status: "active",   dateCreated: new Date("2024-03-18"), totalNotes: 10, NotesFinished: 8 },
  { id: 39, name: "Wyatt Phillips",    email: "wyatt.phillips@example.com",    role: "User",  status: "inactive", dateCreated: new Date("2024-03-22"), totalNotes: 5,  NotesFinished: 1 },
  { id: 40, name: "Victoria Campbell", email: "victoria.campbell@example.com", role: "User",  status: "active",   dateCreated: new Date("2024-03-27"), totalNotes: 14, NotesFinished: 11 },
]);


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
    const pieConfig: ChartConfiguration<'pie'> = {
      type: 'pie',
      data: {
        labels: ['Active Users', 'Inactive Users'],
        datasets: [
          {
            label: 'Users',
            data: [this.usersActiveCount(), this.usersInactiveCount()],
            backgroundColor: ['#5B21B6', '#A78BFA'],
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
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

    this.chart = new Chart(this.myChart.nativeElement, pieConfig);
    this.chart2 = new Chart(this.myChart2.nativeElement, lineConfig);
  }
}
