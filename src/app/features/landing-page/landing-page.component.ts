import { Component, OnInit } from '@angular/core';
import { RouterLink } from "@angular/router";
import AOS from 'aos';

@Component({
  selector: 'app-landing-page',
  imports: [RouterLink],
  templateUrl: './landing-page.component.html',
  styleUrl: './landing-page.component.css'
})
export class LandingPageComponent implements OnInit {

  ngOnInit() {
    AOS.init({
      duration: 1000,
      easing: 'ease-in-out',
      once: true,
    });
  }
}
