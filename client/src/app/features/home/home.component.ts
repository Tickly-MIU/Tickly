import { Component, ElementRef, ViewChild, ViewChildren, QueryList, AfterViewInit, Renderer2 } from '@angular/core';

@Component({
  selector: 'app-home',
  imports: [],
  templateUrl: './home.component.html',
  styleUrl: './home.component.css'
})
export class HomeComponent {


  @ViewChild('todoColumn') todoColumn!: ElementRef;
  @ViewChild('inProgressColumn') inProgressColumn!: ElementRef;
  @ViewChild('doneColumn') doneColumn!: ElementRef;
  @ViewChildren('colorTrigger') colorTriggers!: QueryList<ElementRef>;
  @ViewChildren('colorPicker') colorPickers!: QueryList<ElementRef>;
  @ViewChildren('countElement') countElements!: QueryList<ElementRef>;

  columns: HTMLElement[] = [];

  constructor(private renderer: Renderer2) {}

  ngAfterViewInit(): void {
    // Prepare columns
    this.columns = [
      this.todoColumn.nativeElement,
      this.inProgressColumn.nativeElement,
      this.doneColumn.nativeElement
    ];

    // Initialize Sortable for each column
    // this.columns.forEach(column => {
    //   new Sortable(column, {
    //     group: 'tasks',
    //     animation: 150,
    //     ghostClass: 'sortable-ghost',
    //     chosenClass: 'sortable-chosen',
    //     onSort: () => this.updateCounts()
    //   });
    // });

    // Handle color pickers
    this.colorTriggers.forEach(trigger => {
      this.renderer.listen(trigger.nativeElement, 'click', () => {
        const target = trigger.nativeElement.getAttribute('data-target');
        const picker = document.getElementById(`color-picker-${target}`);

        // Hide other pickers
        this.colorPickers.forEach(p => {
          if (p.nativeElement !== picker) {
            this.renderer.setStyle(p.nativeElement, 'display', 'none');
          }
        });

        // Toggle the selected picker
        const currentDisplay = picker?.style.display;
        this.renderer.setStyle(picker, 'display', currentDisplay === 'block' ? 'none' : 'block');
      });
    });

    // Color selection
    document.querySelectorAll('.color-option').forEach(option => {
      this.renderer.listen(option, 'click', () => {
        const color = option.getAttribute('data-color');
        const picker = option.closest('.color-picker') as HTMLElement;
        const target = picker.id.replace('color-picker-', '');
        document.documentElement.style.setProperty(`--${target}-color`, color!);
        this.renderer.setStyle(picker, 'display', 'none');
      });
    });

    // Close color pickers when clicking outside
    this.renderer.listen('document', 'click', (e: Event) => {
      const target = e.target as HTMLElement;
      if (!target.closest('.color-trigger') && !target.closest('.color-picker')) {
        this.colorPickers.forEach(picker => {
          this.renderer.setStyle(picker.nativeElement, 'display', 'none');
        });
      }
    });

    // Editable headers
    document.querySelectorAll('h3[contenteditable="true"]').forEach(header => {
      this.renderer.listen(header, 'blur', () => {
        const currentText = header.textContent ?? '';
        const column = header.parentElement?.nextElementSibling as HTMLElement;
        const taskCount = column?.children.length ?? 0;
        header.textContent = currentText.replace(/\d+$/, '') + taskCount;
      });
    });

    // Floating add button
    const floatingBtn = document.querySelector('.floating-add-btn');
    if (floatingBtn) {
      this.renderer.listen(floatingBtn, 'click', () => {
        this.renderer.setStyle(floatingBtn, 'transform', 'scale(0.9)');
        setTimeout(() => {
          this.renderer.setStyle(floatingBtn, 'transform', 'scale(1)');
        }, 150);
        alert('Add new task functionality would go here!');
      });
    }

    // Initial count update
    this.updateCounts();
  }

  // Update task counters dynamically
  updateCounts(): void {
    this.columns.forEach((column, index) => {
      const taskCount = column.children.length;
      const countEl = this.countElements.toArray()[index]?.nativeElement;
      if (countEl) {
        countEl.textContent = taskCount;
      }

      const header = column.parentElement?.querySelector('h3');
      if (header) {
        const currentText = header.textContent ?? '';
        header.textContent = currentText.replace(/\d+$/, '') + taskCount;
      }
    });
  }



}
