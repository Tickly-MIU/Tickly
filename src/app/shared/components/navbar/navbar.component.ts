import { Component, inject, signal, computed, OnInit } from '@angular/core';
import { RouterLink, RouterLinkActive, Router } from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
import { ReminderService } from '../../../core/services/reminder.service';
import { Reminder } from '../../../core/models/reminder.interface';
import { CommonModule, DatePipe } from '@angular/common';

@Component({
  selector: 'app-navbar',
  imports: [RouterLink, RouterLinkActive, CommonModule, DatePipe],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.css'
})
export class NavbarComponent implements OnInit {
  router = inject(Router);
  AuthService = inject(AuthService);
  reminderService = inject(ReminderService);
  
  reminders = signal<Reminder[]>([]);
  dropdownOpen = signal<boolean>(false);
  
  ngOnInit() {
    this.loggedIn.set(localStorage.getItem('userId')!==null);
    if (this.loggedIn()) {
      this.loadReminders();
    }
  }
  
  // Use the AuthService.logged signal directly
  loggedIn = this.AuthService.logged;

  // Computed signal for admin role
  isAdmin = computed(() => localStorage.getItem('userRole') === 'admin');

  loadReminders() {
    this.reminderService.getReminders().subscribe({
      next: (reminders) => {
        this.reminders.set(reminders);
      },
      error: (err) => {
        console.error('Failed to load reminders:', err);
      }
    });
  }

  toggleDropdown() {
    this.dropdownOpen.set(!this.dropdownOpen());
    if (this.dropdownOpen() && this.reminders().length === 0) {
      this.loadReminders();
    }
  }

  refreshReminders() {
    this.loadReminders();
  }

  deleteReminders() {
    this.reminderService.deleteReminders().subscribe({
      next: (res) => {
        console.log('Reminders deleted successfully:', res);
        this.loadReminders(); // Refresh the list after deletion
      },
      error: (err) => {
        console.error('Failed to delete reminders:', err);
      }
    });
  }

  logout() {
    localStorage.removeItem('userId');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userRole');
    this.AuthService.logout().subscribe({
      next: (res) => {
        console.log('Logout successful:', res);
      },
      error: (err) => {
        console.error('Logout failed:', err);
      }
    });
    this.AuthService.logged.set(false);
    this.router.navigate(['/landing-page']);
    // isAdmin will update automatically due to computed
  }
}
