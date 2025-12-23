import { Component, inject, OnInit } from '@angular/core';
import { ApiService } from '../../api.service';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-profile',
  imports: [CommonModule],
  templateUrl: './profile.component.html',
  styleUrl: './profile.component.css'
})
export class ProfileComponent implements OnInit {
  private apiService = inject(ApiService);
  
  loading = false;
  profile: any = null;
  error = '';

  ngOnInit() {
    this.loadProfile();
  }

  loadProfile() {
    this.loading = true;
    this.error = '';
    this.apiService.getProfile().subscribe({
      next: (res: any) => {
        this.loading = false;
        if (res.success && res.data) {
          this.profile = res.data;
        } else {
          this.error = res.message || 'Failed to load profile';
        }
      },
      error: (err) => {
        this.loading = false;
        this.error = err.error?.message || err.message || 'Failed to load profile';
      }
    });
  }
}
