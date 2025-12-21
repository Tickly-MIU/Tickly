import { Component, inject, OnInit } from '@angular/core';
import { RouterLink , RouterLinkActive ,Router} from '@angular/router';

@Component({
  selector: 'app-navbar',
  imports: [RouterLink ,RouterLinkActive],
  templateUrl: './navbar.component.html',
  styleUrl: './navbar.component.css'
})
export class NavbarComponent implements OnInit {
  router=inject(Router);
  loggedIn = true;
  isAdmin = true;
  ngOnInit() {
    // const token = localStorage.getItem('token');
    // this.loggedIn = !!token;
    // const role = localStorage.getItem('userRole');
    // this.isAdmin = role === 'admin';
  }
  logout() {
    localStorage.removeItem('token');
    localStorage.removeItem('userRole');
    this.loggedIn = false;
    this.isAdmin = false;
    this.router.navigate(['/landing-page']);
  }
}
