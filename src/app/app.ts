import { Component, OnInit, OnDestroy } from '@angular/core';
import * as AOS from 'aos';
import { RouterOutlet } from '@angular/router';
import { initFlowbite } from 'flowbite';
import { LandingPageComponent } from './features/landing-page/landing-page.component';
import { FooterComponent } from "./shared/components/footer/footer.component";

@Component({
  selector: 'app-root',
  imports: [RouterOutlet, LandingPageComponent, FooterComponent],
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
