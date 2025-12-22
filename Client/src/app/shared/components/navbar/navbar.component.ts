import { Component, inject, OnInit } from '@angular/core';
import { RouterLink , RouterLinkActive ,Router} from '@angular/router';
import { AuthService } from '../../../core/services/auth.service';
@Component({
  selector: 'app-navbar',
  imports: [RouterLink ,RouterLinkActive],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.css'
})
export class NavbarComponent implements OnInit {
  router=inject(Router);
  AuthService=inject(AuthService);
  loggedIn : boolean=false;
  isAdmin : boolean = true;
  ngOnInit() {
    const token = localStorage.getItem('userId');
    this.loggedIn = !!token;
    const role = localStorage.getItem('userRole');
    this.isAdmin = role === 'admin';
  }
  logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('userRole');
    this.loggedIn = false;
    this.isAdmin = false;
    this.AuthService.logout();
    this.router.navigate(['/landing-page']);
  }
}
