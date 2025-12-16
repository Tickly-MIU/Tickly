import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from './environment/environment';

// Base URL of the PHP backend; API_BASE already includes /api, so endpoints are built as `${API_BASE}/...`
const API_BASE = environment.API_BASE;

@Injectable({ providedIn: 'root' })
export class ApiService {
  constructor(private http: HttpClient) {}

  register(body: { full_name: string; email: string; password: string }) {
    return this.http.post(`${API_BASE}/register`, body, { withCredentials: true });
  }

  login(body: { email: string; password: string }) {
    return this.http.post(`${API_BASE}/login`, body, { withCredentials: true });
  }

  logout() {
    return this.http.post(`${API_BASE}/logout`, {}, { withCredentials: true });
  }

  getTasks() {
    return this.http.get(`${API_BASE}/tasks`, { withCredentials: true });
  }

  getTask(task_id: number) {
    return this.http.post(
      `${API_BASE}/tasks/show`,
      { task_id },
      { withCredentials: true }
    );
  }

  createTask(body: any) {
    return this.http.post(`${API_BASE}/tasks/create`, body, { withCredentials: true });
  }

  updateTask(body: any) {
    return this.http.post(`${API_BASE}/tasks/update`, body, { withCredentials: true });
  }

  deleteTask(task_id: number) {
    return this.http.post(`${API_BASE}/tasks/delete`, { task_id }, { withCredentials: true });
  }

  getProfile() {
    return this.http.get(`${API_BASE}/profile`, { withCredentials: true });
  }

  checkSession() {
    return this.http.get(`${API_BASE}/session-check`, { withCredentials: true });
  }
}
