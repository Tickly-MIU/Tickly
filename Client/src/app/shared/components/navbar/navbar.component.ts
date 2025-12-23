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
  
  // Use the AuthService.logged signal directly
  loggedIn = this.AuthService.logged;

  // Computed signal for admin role
  isAdmin = computed(() => {
    const role = localStorage.getItem('userRole');
    return role === 'admin' || role === 'Admin';
  });

  // Computed signal for regular user (not admin)
  isRegularUser = computed(() => {
    return this.loggedIn() && !this.isAdmin();
  });

  // Get user name from localStorage
  userName = computed(() => {
    return localStorage.getItem('userName') || localStorage.getItem('userEmail') || 'User';
  });

  // Get user email
  userEmail = computed(() => {
    return localStorage.getItem('userEmail') || '';
  });
  
  ngOnInit() {
    // Check initial login state
    const userId = localStorage.getItem('userId');
    this.loggedIn.set(userId !== null);
    
    // Load reminders if logged in
    if (this.loggedIn()) {
      this.loadReminders();
    }

    // Listen for storage changes (e.g., when user logs in/out in another tab)
    window.addEventListener('storage', () => {
      const userId = localStorage.getItem('userId');
      this.loggedIn.set(userId !== null);
      if (this.loggedIn()) {
        this.loadReminders();
      } else {
        this.reminders.set([]);
      }
    });
  }

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

  logout() {
    this.AuthService.logout().subscribe({
      next: (res) => {
        console.log('Logout successful:', res);
      },
      error: (err) => {
        console.error('Logout failed:', err);
      }
    });
    
    // Clear local storage
    localStorage.removeItem('userId');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userRole');
    
    // Update logged state
    this.AuthService.logged.set(false);
    
    // Clear reminders
    this.reminders.set([]);
    this.dropdownOpen.set(false);
    
    // Navigate to landing page
    this.router.navigate(['/landing-page']);
  }
}

