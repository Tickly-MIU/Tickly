

import { Component, signal, inject, OnInit } from '@angular/core';
import { Task, ITaskForm } from '../../core/models/task.interface';
import { TaskService } from '../../core/services/task.service';
import { AuthService } from '../../core/services/auth.service';
import { profile } from '../../core/models/user.interface';
import { FormControl, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-profile',
  imports: [CommonModule, ReactiveFormsModule],
  templateUrl: './profile.component.html',
  styleUrl: './profile.component.css'
})
export class ProfileComponent implements OnInit {
  AuthService = inject(AuthService);
  user = signal<profile | null>(null);
  profileLoading = signal(true);

  // Delete task logic
  deleteTask() {
    if (this.loading()) return;
    this.loading.set(true);
    this.TaskService.deleteTask(this.selectedTask!.task_id).subscribe({
      next: (response: any) => {
        this.Tasks.set(
          this.Tasks().filter((t: Task) => t.task_id !== this.selectedTask!.task_id)
        );
        this.updateTaskLists();
        setTimeout(() => {
          this.loading.set(false);
          this.closeDel();
        }, 1000);
      },
      error: (error: Error) => {
        console.error('Error deleting task:', error);
        setTimeout(() => {
          this.loading.set(false);
        }, 1000);
      }
    });
  }
  TaskService = inject(TaskService);
  Tasks = signal<Task[]>([]);
  tasks: Task[] = [];
  pendingTasks: Task[] = [];
  completedTasks: Task[] = [];
  tasksLoading = signal(true);
  
  ngOnInit() {
    this.loadProfile();
    this.loadTasks();
  }

  loadProfile() {
    this.profileLoading.set(true);
    this.AuthService.getProfile().subscribe({
      next: (res: any) => {
        // Accepts either { data: { ...profile } } or just { ...profile }
        let profileData = res && res.data ? res.data : res;
        this.user.set(profileData);
        this.profileLoading.set(false);
      },
      error: (error: Error) => {
        console.error('Error fetching user profile:', error);
        this.profileLoading.set(false);
      }
    });
  }
  userName() {
    const userData = this.user();
    if (!userData) return '';
    // Handle both 'name' and 'full_name' fields
    return userData.name || userData.full_name || '';
  }
  userEmail() {
    return this.user()?.email || '';
  }
  userCreatedAt() {
    const date = this.user()?.created_at;
    if (!date) return '';
    // Format date as YYYY-MM-DD or locale string
    return new Date(date).toLocaleDateString();
  }


  loadTasks() {
    this.tasksLoading.set(true);
    this.TaskService.getTasks().subscribe({
      next: (res: any) => {
        this.tasks = res.data;
        this.Tasks.set(this.tasks);
        this.updateTaskLists();
        this.tasksLoading.set(false);
      },
      error: (error: Error) => {
        console.error('Error fetching tasks:', error);
        this.tasksLoading.set(false);
      }
    });
  }

  updateTaskLists() {
    this.pendingTasks = this.Tasks().filter(t => t.status === 'pending');
    this.completedTasks = this.Tasks().filter(t => t.status === 'completed');
  }

  isExpired(deadline?: Date): boolean {
    if (!deadline) return false;
    const deadlineDate = new Date(deadline);
    const currentDate = new Date();
    return deadlineDate < currentDate;
  }

  // For UI compatibility with home.component.html
  finishingTaskId: number | null = null;
  showModal = signal(false);
  loading = signal(false);
  selectedTask: Task | null = null;
  modalDel = signal(false);

  openModal(task?: Task) {
    this.showModal.set(true);
    this.selectedTask = task || null;
    if (task) {
      this.TaskForm.patchValue({
        title: task.title || '',
        description: task.description || '',
        priority: task.priority || 'medium',
        deadline: task.deadline || null,
      });
    } else {
      this.TaskForm.reset();
      this.TaskForm.patchValue({ priority: 'medium' });
    }
  }
  closeModal() {
    this.showModal.set(false);
    this.selectedTask = null;
  }
  openDeleteModal(task: Task) {
    this.modalDel.set(true);
    this.selectedTask = task;
  }
  closeDel() {
    this.modalDel.set(false);
    this.selectedTask = null;
  }

  finishTimeout: any = null;
  // finishingTaskId already declared above, remove duplicate

  // Update a task (edit)
  UpdateTask() {
    if (this.loading()) return;
    let updatedTask: Task = {
      task_id: this.selectedTask!.task_id,
      user_id: this.selectedTask!.user_id,
      ...this.TaskForm.value,
      status: this.selectedTask!.status as 'pending' | 'completed',
      created_at: this.selectedTask!.created_at,
      updated_at: new Date().toISOString()
    };
    this.loading.set(true);
    this.TaskService.updateTask(updatedTask).subscribe({
      next: (response: any) => {
        const updated = { ...updatedTask, ...response.data };
        this.Tasks.set(
          this.Tasks().map(t => t.task_id === updated.task_id ? updated : t)
        );
        this.updateTaskLists();
        setTimeout(() => {
          this.loading.set(false);
          this.closeModal();
        }, 1000);
      },
      error: (error: Error) => {
        console.error('Error updating task:', error);
        setTimeout(() => {
          this.loading.set(false);
        }, 1000);
      }
    });
  }

  // Mark completed as pending
  markAsPending(task: Task) {
    if (this.loading()) return;
    const updatedTask: Task = { ...task, status: 'pending' as 'pending', updated_at: new Date() };
    this.loading.set(true);
    this.TaskService.updateTask(updatedTask).subscribe({
      next: (response: any) => {
        const updated = { ...updatedTask, ...response.data };
        this.Tasks.set(
          this.Tasks().map(t => t.task_id === updated.task_id ? updated : t)
        );
        this.updateTaskLists();
        setTimeout(() => {
          this.loading.set(false);
        }, 1000);
      },
      error: (error: Error) => {
        console.error('Error updating task:', error);
        setTimeout(() => {
          this.loading.set(false);
        }, 1000);
      }
    });
  }

  // Mark pending as completed (with gray cover)
  finishTask(task: Task) {
    this.finishingTaskId = task.task_id;
    this.finishTimeout = setTimeout(() => {
      const updatedTask: Task = { ...task, status: 'completed' as 'completed', updated_at: new Date() };
      this.TaskService.updateTask(updatedTask).subscribe({
        next: (response: any) => {
          const updated = { ...updatedTask, ...response.data };
          this.Tasks.set(
            this.Tasks().map(t => t.task_id === updated.task_id ? updated : t)
          );
          this.updateTaskLists();
          this.finishingTaskId = null;
          this.finishTimeout = null;
        },
        error: (error: Error) => {
          console.error('Error marking task as completed:', error);
          this.finishingTaskId = null;
          this.finishTimeout = null;
        }
      });
    }, 2000);
  }
  undoFinishTask() {
    if (this.finishTimeout) {
      clearTimeout(this.finishTimeout);
      this.finishTimeout = null;
      this.finishingTaskId = null;
    }
  }

  // Task form for editing
  TaskForm: FormGroup = new FormGroup({
    title: new FormControl('', [Validators.required]),
    description: new FormControl(''),
    priority: new FormControl('medium', [Validators.required]),
    deadline: new FormControl<Date | null>(null),
  });
  get title() { return this.TaskForm.get('title'); }
  get description() { return this.TaskForm.get('description'); }
  get priority() { return this.TaskForm.get('priority'); }
  get deadline() { return this.TaskForm.get('deadline'); }
}

