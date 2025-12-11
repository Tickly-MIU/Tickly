import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';

// Point to the PHP backend under the Tickly public dir (default XAMPP/Apache path).
// If you run `php -S localhost:8000 -t public` instead, switch this to that URL.
const API_BASE = 'http://localhost/Tickly/public';

@Injectable({ providedIn: 'root' })
export class ApiService {
  constructor(private http: HttpClient) {}

  register(body: { full_name: string; email: string; password: string }) {
    return this.http.post(`${API_BASE}/api/register`, body, { withCredentials: true });
  }

  login(body: { email: string; password: string }) {
    return this.http.post(`${API_BASE}/api/login`, body, { withCredentials: true });
  }

  logout() {
    return this.http.post(`${API_BASE}/api/logout`, {}, { withCredentials: true });
  }

  getTasks() {
    return this.http.get(`${API_BASE}/api/tasks`, { withCredentials: true });
  }

  getTask(task_id: number) {
    return this.http.post(
      `${API_BASE}/api/tasks/show`,
      { task_id },
      { withCredentials: true }
    );
  }

  createTask(body: any) {
    return this.http.post(`${API_BASE}/api/tasks/create`, body, { withCredentials: true });
  }

  updateTask(body: any) {
    return this.http.post(`${API_BASE}/api/tasks/update`, body, { withCredentials: true });
  }

  deleteTask(task_id: number) {
    return this.http.post(
      `${API_BASE}/api/tasks/delete`,
      { task_id },
      { withCredentials: true }
    );
  }
}

