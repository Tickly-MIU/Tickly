import { Task,ITaskForm } from './../../core/models/task.interface';
import { FormControl, FormGroup, ReactiveFormsModule,Validators } from '@angular/forms';
import { Component, signal,inject,OnInit, computed} from '@angular/core';
import { CommonModule } from '@angular/common';
import { TaskService } from '../../core/services/task.service';
import { AuthService } from '../../core/services/auth.service';
import { CategoryService } from '../../core/services/category.service';
import { Router } from '@angular/router';
import { filter, map } from 'rxjs';
import { category } from '../../core/models/user.interface';
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
  CategoryService=inject(CategoryService);
  
  ngOnInit() {
    this.loadTasks();
    this.loadCategories();
  }
  
  sessionValid: boolean = false;
  Tasks =signal<Task[]>([]);
  tasks: Task[] = [];
  categories = signal<category[]>([]);
  selectedCategoryFilter = signal<number | null>(null);
  
  // Filtered tasks based on category
  filteredTasks = computed(() => {
    const tasks = this.Tasks();
    const categoryFilter = this.selectedCategoryFilter();
    if (!categoryFilter) return tasks;
    return tasks.filter(task => task.category_id === categoryFilter);
  });

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
  showCategoryModal = signal(false);
  showCategoryEditModal = signal(false);
  loading= signal(false);
  categoryLoading = signal(false);
  
  selectedCategory: category | null = null;
  
  CategoryForm = new FormGroup({
    category_name: new FormControl('', [Validators.required, Validators.minLength(2)])
  });

  
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
        category_id: task.category_id || null,
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
      category_id: this.TaskForm.value.category_id || undefined,
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
      category_id: this.TaskForm.value.category_id || undefined,
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
    category_id: new FormControl<number | null>(null),
  });

  get title() { return this.TaskForm.get('title'); }
  get description() { return this.TaskForm.get('description'); }
  get priority() { return this.TaskForm.get('priority'); }
  get deadline() { return this.TaskForm.get('deadline'); }
  get category_id() { return this.TaskForm.get('category_id'); }

  // Category Management
  loadCategories() {
    this.CategoryService.readCategories().subscribe({
      next: (response: any) => {
        const categoriesData = response.data || response || [];
        this.categories.set(categoriesData);
      },
      error: (error) => {
        console.error('Error fetching categories:', error);
      }
    });
  }

  openCategoryModal() {
    this.CategoryForm.reset();
    this.showCategoryModal.set(true);
  }

  closeCategoryModal() {
    this.showCategoryModal.set(false);
    this.CategoryForm.reset();
  }

  createCategory() {
    if (this.CategoryForm.valid && !this.categoryLoading()) {
      this.categoryLoading.set(true);
      const userId = parseInt(localStorage.getItem('userId') || localStorage.getItem('user_id') || '0');
      const categoryData: { user_id: number; category_name: string; category_id?: number } = {
        user_id: userId,
        category_name: this.CategoryForm.value.category_name!
      };
      // category_id is typically auto-generated by backend, but can be included if backend requires it
      // For create operations, it's usually omitted and generated by the server
      this.CategoryService.createCategory(categoryData).subscribe({
        next: (response: any) => {
          console.log('Category created:', response);
          this.loadCategories();
          this.closeCategoryModal();
          this.categoryLoading.set(false);
        },
        error: (error) => {
          console.error('Error creating category:', error);
          this.categoryLoading.set(false);
        }
      });
    }
  }

  openCategoryEditModal(category: category) {
    this.selectedCategory = category;
    this.CategoryForm.patchValue({ category_name: category.category_name });
    this.showCategoryEditModal.set(true);
  }

  closeCategoryEditModal() {
    this.showCategoryEditModal.set(false);
    this.selectedCategory = null;
    this.CategoryForm.reset();
  }

  updateCategory() {
    if (this.CategoryForm.valid && this.selectedCategory && !this.categoryLoading()) {
      this.categoryLoading.set(true);
      this.CategoryService.updateCategory({
        category_id: this.selectedCategory.category_id,
        category_name: this.CategoryForm.value.category_name!
      }).subscribe({
        next: (response: any) => {
          console.log('Category updated:', response);
          this.loadCategories();
          this.closeCategoryEditModal();
          this.categoryLoading.set(false);
        },
        error: (error) => {
          console.error('Error updating category:', error);
          this.categoryLoading.set(false);
        }
      });
    }
  }

  deleteCategory(categoryId: number) {
    if (confirm('Are you sure you want to delete this category? Tasks in this category will be unassigned.')) {
      this.categoryLoading.set(true);
      this.CategoryService.deleteCategory(categoryId).subscribe({
        next: (response: any) => {
          console.log('Category deleted:', response);
          this.loadCategories();
          // Update tasks to remove category_id
          this.Tasks.update(tasks => 
            tasks.map(task => 
              task.category_id === categoryId ? { ...task, category_id: undefined } : task
            )
          );
          this.categoryLoading.set(false);
        },
        error: (error) => {
          console.error('Error deleting category:', error);
          this.categoryLoading.set(false);
        }
      });
    }
  }

  filterByCategory(categoryId: number | null) {
    this.selectedCategoryFilter.set(categoryId);
  }

  getCategoryName(categoryId?: number): string {
    if (!categoryId) return 'Uncategorized';
    const category = this.categories().find(c => c.category_id === categoryId);
    return category?.category_name || 'Unknown';
  }
}
