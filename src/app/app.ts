import { Component, OnInit, OnDestroy } from '@angular/core';
import * as AOS from 'aos';
import { initFlowbite } from 'flowbite';
import { RouterOutlet } from '@angular/router';
import { FooterComponent } from "./shared/components/footer/footer.component";
import { NavbarComponent } from "./shared/components/navbar/navbar.component";

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, FooterComponent, NavbarComponent],
  templateUrl: './app.html',
  styleUrl: './app.css'
})
export class App implements OnInit{
  protected title = 'ToDoProject';
  ngOnInit(): void {
    initFlowbite();
    AOS.init({
      duration: 1000,
    });
  }
  ngOnDestroy() {
    AOS.refreshHard();
  }
}
