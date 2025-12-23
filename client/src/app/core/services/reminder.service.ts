import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { environment } from '../../environment/environment';
import { Reminder } from '../models/reminder.interface';
import { Observable } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class ReminderService {
  http = inject(HttpClient);
  private API_BASE = environment.API_BASE;

  getReminders(): Observable<any> {
    return this.http.get(`${this.API_BASE}/reminders`, { withCredentials: true });
  }

  getReminder(reminder_id: number): Observable<Reminder> {
    return this.http.post<Reminder>(
      `${this.API_BASE}/reminders/show`,
      { reminder_id },
      { withCredentials: true }
    );
  }
}

