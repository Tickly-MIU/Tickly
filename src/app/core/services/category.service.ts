import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { environment } from '../../environment/environment';

const API_BASE = environment.API_BASE;

@Injectable({
  providedIn: 'root'
})
export class CategoryService {
  http = inject(HttpClient);

  createCategory(categoryData: { user_id: number; category_name: string; category_id?: number }): Observable<any> {
    return this.http.post(`${API_BASE}/category/create`, categoryData, { withCredentials: true });
  }

  readCategories(): Observable<any> {
    return this.http.post(`${API_BASE}/category/read`, {}, { withCredentials: true });
  }

  updateCategory(categoryData: { category_id: number; category_name: string }): Observable<any> {
    return this.http.post(`${API_BASE}/category/update`, categoryData, { withCredentials: true });
  }

  deleteCategory(category_id: number): Observable<any> {
    return this.http.post(`${API_BASE}/category/delete`, { category_id }, { withCredentials: true });
  }
}

