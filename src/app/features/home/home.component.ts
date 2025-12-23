import { Task,ITaskForm } from './../../core/models/task.interface';
import { FormControl, FormGroup, ReactiveFormsModule,Validators } from '@angular/forms';
import { Component, signal,inject,OnInit} from '@angular/core';
import { CommonModule } from '@angular/common';
import { TaskService } from '../../core/services/task.service';
import { AuthService } from '../../core/services/auth.service';
import { Router } from '@angular/router';
import { filter, map } from 'rxjs';
@Component({
  selector: 'app-home',
  imports: [ReactiveFormsModule, CommonModule],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent implements OnInit {
  Router=inject(Router);
  AuthService=inject(AuthService);
  TaskService=inject(TaskService);
  ngOnInit() {
    this.loadTasks();
  }
  sessionValid: boolean = false;
  Tasks =signal<Task[]>([]);
  tasks: Task[] = [];

  loadTasks() {
    this.TaskService.getTasks().pipe(
  map(res => ({
    ...res,
    data: res.data.filter((task: { status: string; }) => task.status === 'pending')
  }))
).subscribe({
      next: (res) => {
        console.log('Tasks loaded:', res.data);
        this.tasks = res.data;
    this.Tasks.set(this.tasks);

      },
      error: (error:Error) => {
        console.error('Error fetching tasks:', error);
      }
    });
  }

  showModal = signal(false);
  loading= signal(false);

  
  isExpired(deadline?: Date): boolean {
    if (!deadline) {
      return false;
    }
    const deadlineDate = new Date(deadline);
    const currentDate = new Date();
    return deadlineDate < currentDate;
  }
  
  selectedTask: Task | null = null;

  finishingTaskId: number | null = null;
  finishTimeout: any = null;
  undoFinishTask() {
    if (this.finishTimeout) {
      clearTimeout(this.finishTimeout);
      this.finishTimeout = null;
      this.finishingTaskId = null;
      // Optionally, update UI if needed
    }
  }

  finishTask(task: Task) {
    // Show gray cover immediately
    this.finishingTaskId = task.task_id;
    // Start a delay before actually finishing
    this.finishTimeout = setTimeout(() => {
      task.status = 'completed';
      this.TaskService.updateTask(task).subscribe({
        next: (response: any) => {
          console.log('Task marked as completed:', response);
          this.Tasks.set(
            this.Tasks().map(t => t.task_id === task.task_id ? { ...t, status: 'completed' } : t)
          );
          this.finishingTaskId = null;
          this.finishTimeout = null;
        },
        error: (error: Error) => {
          console.error('Error marking task as completed:', error);
          this.finishingTaskId = null;
          this.finishTimeout = null;
        }
      });
    }, 2000); // 2 seconds delay
  }
  
  openModal(task?: Task) {
    this.showModal.set(true);
    this.selectedTask = task || null;
    if (task) {
      this.TaskForm.patchValue({
        title: task.title || '',
        description: task.description || '',
        priority: task.priority || 'medium',
        deadline: task.deadline|| null,
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

  modalDel=signal(false);

  openDeleteModal(task: Task) {
    this.modalDel.set(true);
    this.selectedTask = task;
  }

  closeDel() {
    this.modalDel.set(false);
    this.selectedTask = null;
  }

  deleteTask() {
    if(this.loading()){
      return;
    }
    this.loading.set(true);
    this.TaskService.deleteTask(this.selectedTask!.task_id).subscribe({
      next: (response:any) => {
        console.log('Task deleted successfully:', response);
        // Remove the deleted task from the Tasks signal
        this.Tasks.set(
          this.Tasks().filter(t => t.task_id !== this.selectedTask!.task_id)
        );
        setTimeout(() => {
          this.loading.set(false);
          this.closeDel();
        }, 2000);
      },
      error: (error:Error) => {
        console.error('Error deleting task:', error); 
        setTimeout(() => {
          this.loading.set(false);
        }, 2000);
      }
    });

  }


  CreateTask() {
    if(this.loading()){
      return;
    }

    let createdTask: ITaskForm = {
      user_id: parseInt(localStorage.getItem('user_id')!),
      ...this.TaskForm.value,
      status: 'pending',
      created_at: new Date().toISOString()
    };
    this.loading.set(true);
    this.TaskService.createTask(createdTask).subscribe({
      next: (response:any) => {
        console.log('Task created successfully:', response);
        // Add the new task to the Tasks signal
        const newTask = { ...createdTask, ...response.data };
        this.Tasks.set([...this.Tasks(), newTask]);
        setTimeout(() => {
          this.loading.set(false);
          this.closeModal();
        }, 2000);
      },
      error: (error:Error) => {
        console.error('Error creating task:', error);
        setTimeout(() => {
          this.loading.set(false);
        }, 2000);
      }
    });

  }

  UpdateTask() {
  if(this.loading()){
      return;
    }

    let updatedTask: Task = {
      task_id: this.selectedTask!.task_id,
      user_id: parseInt(localStorage.getItem('user_id')!),
      ...this.TaskForm.value,
      status: 'pending',
      created_at: this.selectedTask!.created_at,
      updated_at: new Date().toISOString()
    };
    this.loading.set(true);
    this.TaskService.updateTask(updatedTask).subscribe({
      next: (response:any) => {
        console.log('Task Updated successfully:', response);
        // Update the task in the Tasks signal
        const updated = { ...updatedTask, ...response.data };
        this.Tasks.set(
          this.Tasks().map(t => t.task_id === updated.task_id ? updated : t)
        );
        setTimeout(() => {
          this.loading.set(false);
          this.closeModal();
        }, 2000);
      },
      error: (error:Error) => {
        console.error('Error updating task:', error);
        setTimeout(() => {
          this.loading.set(false);
        }, 2000);
      }
    });

  }

  handleSubmit() {
    this.selectedTask ? this.UpdateTask() : this.CreateTask();
  }

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
