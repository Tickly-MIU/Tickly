import { Component, OnInit, OnDestroy } from '@angular/core';
import * as AOS from 'aos';
import { RouterOutlet } from '@angular/router';

@Component({
  selector: 'app-root',
  imports: [RouterOutlet],
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
