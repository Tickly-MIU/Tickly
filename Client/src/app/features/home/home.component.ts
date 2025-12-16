import { Component, ElementRef, ViewChild, ViewChildren, QueryList, AfterViewInit, OnInit, inject, Renderer2, ChangeDetectorRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { RouterLink, Router } from '@angular/router';
import { ApiService } from '../../api.service';
import { DatePipe } from '@angular/common';
import { environment } from '../../environment/environment';

interface Task {
  task_id: number;
  user_id: number;
  category_id?: number;
  title: string;
  description?: string;
  priority: 'low' | 'medium' | 'high';
  deadline?: string;
  status: 'pending' | 'completed';
  created_at: string;
  updated_at: string;
}

@Component({
  selector: 'app-home',
  imports: [CommonModule, RouterLink],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css',
  providers: [DatePipe]
})
export class HomeComponent implements OnInit, AfterViewInit {
  private apiService = inject(ApiService);
  private renderer = inject(Renderer2);
  private datePipe = inject(DatePipe);
  private router = inject(Router);
  private cdr = inject(ChangeDetectorRef);

  @ViewChild('todoColumn') todoColumn!: ElementRef;
  @ViewChild('doneColumn') doneColumn!: ElementRef;
  @ViewChildren('colorTrigger') colorTriggers!: QueryList<ElementRef>;
  @ViewChildren('colorPicker') colorPickers!: QueryList<ElementRef>;
  @ViewChildren('countElement') countElements!: QueryList<ElementRef>;

  columns: HTMLElement[] = [];
  
  tasks: Task[] = [];
  loading = false;
  error = '';
  userName = localStorage.getItem('userName') || 'User';

  // Grouped tasks
  todoTasks: Task[] = [];
  doneTasks: Task[] = [];

  ngOnInit(): void {
    console.log('HomeComponent initialized');
    console.log('Page refreshed or navigated to home');
    
    // Check if user data exists in localStorage
    const userId = localStorage.getItem('userId');
    const userName = localStorage.getItem('userName');
    const userEmail = localStorage.getItem('userEmail');
    
    if (!userId || !userName) {
      console.warn('No user data found in localStorage - redirecting to login');
      this.error = 'Please login to view your tasks';
      this.loading = false;
      setTimeout(() => {
        this.router.navigate(['/login']);
      }, 1500);
      return;
    }
    
    // Update userName from localStorage (in case it changed)
    this.userName = userName || 'User';
    
    // Validate session with server before loading tasks
    this.validateSessionAndLoadTasks();
  }

  validateSessionAndLoadTasks(): void {
    console.log('Validating session on page refresh...');
    this.loading = true;
    this.error = '';
    
    // First check if session is still valid
    this.apiService.checkSession().subscribe({
      next: (res: any) => {
        console.log('Session check response:', res);
        
        // Check if session is valid - user_id should be in the response
        // Response structure: { success: true/false, data: { user_id: ..., session_id: ..., is_authenticated: ..., ... } }
        const userId = res?.data?.user_id;
        const sessionId = res?.data?.session_id;
        const isAuthenticated = res?.data?.is_authenticated ?? false;
        const hasSessionCookie = res?.data?.has_session_cookie ?? false;
        
        console.log('Session validation details:', {
          userId,
          sessionId,
          isAuthenticated,
          hasSessionCookie,
          sessionName: res?.data?.session_name
        });
        
        if (res && isAuthenticated && userId) {
          // Session is valid
          console.log('✓ Session valid - User ID:', userId, 'Session ID:', sessionId);
          
          // Verify localStorage matches server session
          const localUserId = localStorage.getItem('userId');
          if (localUserId && localUserId.toString() !== userId.toString()) {
            console.warn('User ID mismatch - updating localStorage');
            localStorage.setItem('userId', userId.toString());
          }
          
          // Load tasks
          this.loadTasks();
        } else {
          // Session invalid or expired
          if (!hasSessionCookie) {
            console.warn('✗ No session cookie found - cookies may not be sent properly');
            console.warn('Make sure the request includes withCredentials: true');
          } else {
            console.warn('✗ Session invalid or expired - user_id is null');
          }
          console.warn('Session check response:', res?.data);
          this.handleSessionExpired();
        }
        
        // Force change detection
        this.cdr.detectChanges();
      },
      error: (err: any) => {
        console.error('Session check failed:', err);
        console.error('Error details:', {
          status: err.status,
          statusText: err.statusText,
          error: err.error
        });
        
        // If 401 or 403, session is definitely expired
        if (err.status === 401 || err.status === 403) {
          console.warn('Session expired (401/403)');
          this.handleSessionExpired();
        } else if (err.status === 0) {
          // Network error - server might be down
          this.loading = false;
          this.error = 'Cannot connect to server. Please check your connection.';
          console.error('Network error - cannot reach server');
          this.cdr.detectChanges();
        } else {
          // Other errors - try to load tasks anyway (might be temporary issue)
          console.log('Session check error (non-auth), attempting to load tasks anyway...');
          this.loadTasks();
        }
      }
    });
  }

  handleSessionExpired(): void {
    this.loading = false;
    this.error = 'Your session has expired. Please login again.';
    
    // Clear localStorage
    localStorage.removeItem('userId');
    localStorage.removeItem('userName');
    localStorage.removeItem('userEmail');
    localStorage.removeItem('userRole');
    
    // Redirect to login after a short delay
    setTimeout(() => {
      this.router.navigate(['/login'], {
        queryParams: { expired: 'true' }
      });
    }, 2000);
  }

  ngAfterViewInit(): void {
    // Prepare columns
    this.columns = [
      this.todoColumn?.nativeElement,
      this.doneColumn?.nativeElement
    ].filter(Boolean);

    // Handle color pickers
    setTimeout(() => {
      this.setupColorPickers();
      this.setupFloatingButton();
    }, 0);
  }

  loadTasks(): void {
    this.loading = true;
    this.error = '';
    console.log('Loading tasks...');
    console.log('API Base URL:', environment.API_BASE);
    console.log('Loading state set to:', this.loading);
    this.cdr.detectChanges(); // Update view immediately to show loading state
    
    // Add timeout to prevent infinite loading
    const timeout = setTimeout(() => {
      if (this.loading) {
        this.loading = false;
        this.error = 'Request timeout. Please check your connection and try again.';
        console.error('Request timeout - API call took longer than 10 seconds');
        console.log('Loading state after timeout:', this.loading);
        this.cdr.detectChanges();
      }
    }, 10000); // 10 second timeout
    
    this.apiService.getTasks().subscribe({
      next: (res: any) => {
        clearTimeout(timeout);
        console.log('API Response received:', res);
        console.log('Response type:', typeof res);
        console.log('Response keys:', Object.keys(res || {}));
        this.loading = false;
        console.log('Loading state set to false after successful response');
        
        // Handle different response structures
        if (res && res.success !== false) {
          // Check if data exists and is an array
          const tasksData = res.data || res.tasks || [];
          console.log('Tasks data:', tasksData);
          
          if (Array.isArray(tasksData)) {
            this.tasks = tasksData as Task[];
            console.log('Tasks loaded:', this.tasks.length, 'tasks');
            this.groupTasks();
            
            if (this.tasks.length === 0) {
              console.log('No tasks found. This is normal if you haven\'t created any tasks yet.');
              this.error = ''; // Clear any previous errors
            }
          } else {
            console.error('Tasks data is not an array:', tasksData);
            this.error = 'Invalid response format from server';
            this.tasks = [];
            this.groupTasks();
          }
        } else {
          // Handle error response
          this.error = res?.message || 'Failed to load tasks';
          console.error('API returned error:', res);
          this.tasks = [];
          this.groupTasks();
        }
        
        // Force change detection to update the view
        this.cdr.detectChanges();
      },
      error: (err: any) => {
        clearTimeout(timeout);
        this.loading = false;
        console.error('HTTP Error occurred:', err);
        console.error('Full error object:', JSON.stringify(err, null, 2));
        console.error('Error details:', {
          status: err.status,
          statusText: err.statusText,
          error: err.error,
          message: err.message,
          url: err.url,
          name: err.name
        });
        
        // Handle specific error cases
        if (err.status === 401 || err.status === 403) {
          console.error('Authentication failed during task load - session expired');
          // Use handleSessionExpired for consistent behavior
          this.handleSessionExpired();
        } else if (err.status === 0) {
          this.error = 'Cannot connect to server. Please check if the server is running at ' + environment.API_BASE;
        } else if (err.status === 404) {
          this.error = 'API endpoint not found. Please check the server configuration.';
        } else {
          const errorMessage = err.error?.message || err.message || 'Failed to load tasks';
          this.error = errorMessage;
          console.error('Error message:', errorMessage);
        }
        
        // Ensure tasks array is empty on error
        this.tasks = [];
        this.groupTasks();
        
        // Force change detection to update the view
        this.cdr.detectChanges();
      }
    });
  }

  groupTasks(): void {
    // Filter tasks by status
    this.todoTasks = this.tasks.filter(t => t.status === 'pending');
    this.doneTasks = this.tasks.filter(t => t.status === 'completed');
    
    // Debug logging to verify filtering
    console.log('Grouped tasks - Pending (In-Progress):', this.todoTasks.length, 'Completed:', this.doneTasks.length);
    console.log('Pending tasks:', this.todoTasks.map(t => ({ id: t.task_id, title: t.title, status: t.status })));
    console.log('All task statuses:', this.tasks.map(t => ({ id: t.task_id, status: t.status })));
    
    // Verify that todoTasks only contains pending tasks
    const nonPendingInTodo = this.todoTasks.filter(t => t.status !== 'pending');
    if (nonPendingInTodo.length > 0) {
      console.error('ERROR: Found non-pending tasks in todoTasks:', nonPendingInTodo);
    } else {
      console.log('✓ Verified: All tasks in todoTasks have status "pending"');
    }
    
    // Force change detection after grouping
    this.cdr.detectChanges();
  }

  updateTaskStatus(task: Task, newStatus: 'pending' | 'completed'): void {
    this.apiService.updateTask({
      task_id: task.task_id,
      status: newStatus
    }).subscribe({
      next: (res: any) => {
        if (res.success) {
          task.status = newStatus;
          this.groupTasks();
        }
      },
      error: (err: any) => {
        console.error('Failed to update task:', err);
      }
    });
  }

  deleteTask(taskId: number): void {
    if (!confirm('Are you sure you want to delete this task?')) {
      return;
    }

    this.apiService.deleteTask(taskId).subscribe({
      next: (res: any) => {
        if (res.success) {
          this.tasks = this.tasks.filter(t => t.task_id !== taskId);
          this.groupTasks();
        }
      },
      error: (err: any) => {
        console.error('Failed to delete task:', err);
        alert('Failed to delete task. Please try again.');
      }
    });
  }

  getPriorityColor(priority: string): string {
    switch (priority) {
      case 'high': return 'bg-red-100 text-red-800';
      case 'medium': return 'bg-yellow-100 text-yellow-800';
      case 'low': return 'bg-green-100 text-green-800';
      default: return 'bg-gray-100 text-gray-800';
    }
  }

  formatDate(dateString?: string): string {
    if (!dateString) return '';
    return this.datePipe.transform(dateString, 'MMM d, y') || '';
  }

  getInitials(name: string): string {
    return name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2);
  }

  setupColorPickers(): void {
    this.colorTriggers.forEach(trigger => {
      this.renderer.listen(trigger.nativeElement, 'click', () => {
        const target = trigger.nativeElement.getAttribute('data-target');
        const picker = document.getElementById(`color-picker-${target}`);

        this.colorPickers.forEach(p => {
          if (p.nativeElement !== picker) {
            this.renderer.setStyle(p.nativeElement, 'display', 'none');
          }
        });

        const currentDisplay = picker?.style.display;
        this.renderer.setStyle(picker, 'display', currentDisplay === 'block' ? 'none' : 'block');
      });
    });

    document.querySelectorAll('.color-option').forEach(option => {
      this.renderer.listen(option, 'click', () => {
        const color = option.getAttribute('data-color');
        const picker = option.closest('.color-picker') as HTMLElement;
        const target = picker.id.replace('color-picker-', '');
        document.documentElement.style.setProperty(`--${target}-color`, color!);
        this.renderer.setStyle(picker, 'display', 'none');
      });
    });

    this.renderer.listen('document', 'click', (e: Event) => {
      const target = e.target as HTMLElement;
      if (!target.closest('.color-trigger') && !target.closest('.color-picker')) {
        this.colorPickers.forEach(picker => {
          this.renderer.setStyle(picker.nativeElement, 'display', 'none');
        });
      }
    });
  }

  setupFloatingButton(): void {
    const floatingBtn = document.querySelector('.floating-add-btn');
    if (floatingBtn) {
      this.renderer.listen(floatingBtn, 'click', () => {
        this.renderer.setStyle(floatingBtn, 'transform', 'scale(0.9)');
        setTimeout(() => {
          this.renderer.setStyle(floatingBtn, 'transform', 'scale(1)');
        }, 150);
        // TODO: Open create task modal
        const title = prompt('Enter task title:');
        if (title) {
          this.createTask(title);
        }
      });
    }
  }

  createTask(title: string): void {
    if (!title || title.trim() === '') {
      return;
    }

    this.apiService.createTask({
      title: title.trim(),
      description: '',
      priority: 'medium',
      status: 'pending'
    }).subscribe({
      next: (res: any) => {
        if (res.success) {
          console.log('Task created successfully');
          this.loadTasks();
        } else {
          console.error('Failed to create task:', res);
          alert(res.message || 'Failed to create task. Please try again.');
        }
      },
      error: (err: any) => {
        console.error('Failed to create task:', err);
        const errorMsg = err.error?.message || err.message || 'Failed to create task. Please try again.';
        alert(errorMsg);
      }
    });
  }

  createFirstTask(): void {
    const title = prompt('Enter your first task title:');
    if (title && title.trim()) {
      this.createTask(title);
    }
  }

  logout(): void {
    console.log('Logging out...');
    
    // Call the logout API
    this.apiService.logout().subscribe({
      next: (res: any) => {
        console.log('Logout successful:', res);
        
        // Clear localStorage
        localStorage.removeItem('userId');
        localStorage.removeItem('userName');
        localStorage.removeItem('userEmail');
        localStorage.removeItem('userRole');
        
        // Navigate to login page
        this.router.navigate(['/login']);
      },
      error: (err: any) => {
        console.error('Logout error:', err);
        
        // Even if API call fails, clear local storage and redirect
        localStorage.removeItem('userId');
        localStorage.removeItem('userName');
        localStorage.removeItem('userEmail');
        localStorage.removeItem('userRole');
        
        // Navigate to login page
        this.router.navigate(['/login']);
      }
    });
  }
}
