import { Injectable,inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environment/environment';
import { Task,ITaskForm } from '../models/task.interface';
import { Observable } from 'rxjs';
@Injectable({
  providedIn: 'root',
})
export class TaskService {
  http = inject(HttpClient);
  private API_BASE = environment.API_BASE;


    getTasks(): Observable<any> {
    return this.http.get(`${this.API_BASE}/tasks`, { withCredentials: true });
  }

  getTask(task_id: number): Observable<Task> {
    return this.http.post<Task>(
      `${this.API_BASE}/tasks/show`,
      { task_id },
      { withCredentials: true }
    );
  }

  deleteTask(task_id: number): Observable<any> {
    return this.http.post(`${this.API_BASE}/tasks/delete`, { task_id }, { withCredentials: true });
  }

  createTask(body: ITaskForm): Observable<any>{
    return this.http.post(`${this.API_BASE}/tasks/create`, body, { withCredentials: true });
  }

  updateTask(body: Task): Observable<any>{
    return this.http.post(`${this.API_BASE}/tasks/update`, body, { withCredentials: true });
  }
}
