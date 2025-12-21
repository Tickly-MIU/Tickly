import { Component} from '@angular/core';
import { Task } from '../../core/models/task';
@Component({
  selector: 'app-home',
  imports: [],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent {
tasks: Task[] = [
  {
    task_id: 1,
    title: 'Complete project report',
    priority: 'high',
    status: 'pending',
    deadline: '2026-06-10',
    created_at: '2024-06-01T10:00:00Z',
    updated_at: '2024-06-01T10:00:00Z'
  },
  {
    task_id: 2,
    title: 'Prepare presentation slides',
    priority: 'medium',
    status: 'completed',
    description: 'Slides for the upcoming client meeting',
    created_at: '2024-06-02',
    updated_at: '2024-06-03'
  },
  {
    task_id: 3,
    title: 'Organize team meeting',
    priority: 'low',
    status: 'pending',
    deadline: '2024-06-10',
    created_at: '2024-06-04T14:30:00Z',
    updated_at: '2024-06-04T14:30:00Z'
  },
  {
    task_id: 4,
        title: 'Organize team meeting',
    priority: 'low',
    status: 'pending',
    deadline: '2024-06-10',
    created_at: '2024-06-04T14:30:00Z',
    updated_at: '2024-06-04T14:30:00Z'
  },
  {
    task_id: 4,
        title: 'Organize team meeting',
    priority: 'low',
    status: 'pending',
    deadline: '2024-06-10',
    created_at: '2024-06-04T14:30:00Z',
    updated_at: '2024-06-04T14:30:00Z'
  },
  {
    task_id: 5,
        title: 'Organize team meeting',
    priority: 'low',
    status: 'pending',
    deadline: '2024-06-10',
    created_at: '2024-06-04T14:30:00Z',
    updated_at: '2024-06-04T14:30:00Z'
  },
  {
    task_id: 6,
        title: 'Organize team meeting',
    priority: 'low',
    status: 'pending',
    deadline: '2024-06-10',
    created_at: '2024-06-04T14:30:00Z',
    updated_at: '2024-06-04T14:30:00Z'
  },
];
 finished = false;
  finishTask(task: Task) {
    this.finished = true;
    task.status = 'completed';
  }
  isExpired(deadline?: string): boolean {
    if (!deadline) {
      return false;
    }
    const deadlineDate = new Date(deadline);
    const currentDate = new Date();
    return deadlineDate < currentDate;
  }

  selectedTask: Task | null = null;

openModal(task: Task) {
  this.selectedTask = task;
}

closeModal() {
  this.selectedTask = null;
}

}
